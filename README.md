<p align="center">SAYNA API</p>


## À propos de l'API

Cette API vous offrira la gestion d'utilisateur avec Laravel et héberger chez heroku.com. Vous pouvez consommer nos service à partir de ces liens ci-dessous :

- Affichage page index.html: GET [ici](https://damp-eyrie-37321.herokuapp.com/api/)
- Login POST, parametre: email, password [ici](https://damp-eyrie-37321.herokuapp.com/api/login).
- Registre POST, parametre: firstname, lastname, date_naissance, sexe, email, password, password_confirmation [ici](https://damp-eyrie-37321.herokuapp.com/api/register).
- Liste d'utilisateur GET  [une utilisateur ici](https://damp-eyrie-37321.herokuapp.com/api/user/{token}) et [plusieur utilisateur ici](https://damp-eyrie-37321.herokuapp.com/api/user/{token}?all) avec autorisation Bearer Token.
- Modifier utilisateur PUT, parametre: firstname, lastname, date_naissance, sexe [ici](https://damp-eyrie-37321.herokuapp.com/api/user/{token}) avec autorisation Bearer Token
- Déconnexion utilisateur DELETE, [ici](https://damp-eyrie-37321.herokuapp.com/api/user/{token}) avec autorisation Bearer Token