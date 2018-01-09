1. Dans css/light-grey.css modifier la balise .caselog_input_header ligne ~1049 :

.caselog_input_header {
  padding-top: 3px;
  padding-bottom: 3px;
  border-top: 1px solid #fff;
  background: #ddd;
  width: 100%;
  height: 42px;
}

2. Créer la notification avec dans le champ A l'OQL : SELECT Person WHERE email REGEXP :this->le_champ_du_ticket

remplacer le_champ_du_ticket par le même champ que celui dans le paramètre fieldForRegEx du fichier de conf