Lancement Docker :

Commandes :

- cp .env.example .env
- docker compose up -d pour lancer API + bases PostgreSQL.

L'API réponds au port : http://localhost:6080

Précisions pour la fonctionnalité 13 : Indisponibilités temporaires d’un praticien :

Endpoint exposé:

POST /praticiens/{id}/indisponibilités, nous avons pris par exemple cet id de praticien : af7bb2f1-cc52-3388-b9bc-c0b89e7f4c5b
Corps de la requête (JSON) :
{  
 "debut": "2025-12-01T14:00:00+00:00",  
 "fin": "2025-12-01T14:30:00+00:00"  
}  
L'accesstoken généré après l'authentification de ce praticien est nécessaire.

Réponse en cas de succès :
Code HTTP 201 Created

Corps JSON :

{
"message": "Indisponibilité enregistrée (non persistée en base de données)",
"praticien_id": "af7bb2f1-cc52-3388-b9bc-c0b89e7f4c5b",
"debut": "2025-12-01T14:00:00+00:00",
"fin": "2025-12-01T14:30:00+00:00",
"\_links": {
"praticien": {
"href": "/praticiens/af7bb2f1-cc52-3388-b9bc-c0b89e7f4c5b"
}
}
}
L’endpoint est protégé par le middleware AuthnMiddleware, qui vérifie le token JWT et enrichit la requête avec un UserProfileDTO (userProfile).​

Seuls les utilisateurs ayant le rôle praticien peuvent appeler cet endpoint.
L’identifiant du praticien dans le token (userProfile->id) doit être égal à l’{id} présent dans l’URL.
Ce comportement suit la politique d’autorisations de l’énoncé: "praticien authentifié = praticien concerné par l’opération".​

Les champs debut et fin sont obligatoires dans le corps JSON.
La date/heure de fin doit être strictement postérieure à la date/heure de début, sinon l’API renvoie 400 Bad Request avec un message d’erreur.

Les scripts SQL fournis pour le projet (toubiprat.schema.sql, toubipat.schema.sql, toubirdv.schema.sql, toubiauth.schema.sql) ne définissent aucune table dédiée aux indisponibilités de praticien.​
Afin de respecter ces schémas sans les modifier, l’implémentation actuelle n’enregistre pas les indisponibilités dans une base de données :
une action CreerIndisponibiliteAction a été crée,
la requête est validée,
les informations sont tracées dans les logs du serveur (via error_log),
la réponse renvoyée indique clairement que l’indisponibilité n’est pas persistée en base.

En conséquence, ces indisponibilités ne sont pas encore prises en compte lors de la création de rendez‑vous et sont perdues lors d’un redémarrage du service.​

Si la modification des schémas SQL était autorisée,nous pourrions ajouter dans la base des rendez‑vous (toubirdv) une table indisponibilite, avec les colonnes :
id (string)
praticien_id (string)​
date_heure_debut (timestamp)
date_heure_fin (timestamp)

L’endpoint POST /praticiens/{id}/indisponibilites écrirait alors dans cette table, et la logique de création de rendez‑vous vérifierait les chevauchements pour refuser un créneau si le praticien est indisponible.​
