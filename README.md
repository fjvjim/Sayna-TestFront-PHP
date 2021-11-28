<p align="center">SAYNA API</p>


## À propos de l'API

Cette API vous offrira la gestion d'utilisateur avec Laravel et héberger chez heroku.com. Vous pouvez consommer nous service à partir de ces liens ci-dessous. :

- Affichage page index.html [GET](https://intense-thicket-34397.herokuapp.com/api/)
- Login POST :[parametre: email, password](https://intense-thicket-34397.herokuapp.com/api/login).
- Registre POST [parametre: firstname, lastname, date_naissance, sexe, email, password, password_confirmation](https://intense-thicket-34397.herokuapp.com/api/register).
- Liste d'utilisateur GET [une utilisateur](https://intense-thicket-34397.herokuapp.com/api/user/{token}) et [plusieur utilisateur](https://intense-thicket-34397.herokuapp.com/api/user/{token}?all) avec authorisation Bearer Token.
- Modifier utilisateur PUT [parametre: firstname, lastname, date_naissance, sexe](https://intense-thicket-34397.herokuapp.com/api/user/{token}) avec authorisation Bearer Token
- Deconnecxion utilisateur [DELETE](https://intense-thicket-34397.herokuapp.com/api/user/{token}) avec authorisation Bearer Token
