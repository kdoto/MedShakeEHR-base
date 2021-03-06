<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * Bertrand Boutillier <b.boutillier@gmail.com>
 * http://www.medshake.net
 *
 * MedShakeEHR is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * MedShakeEHR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MedShakeEHR.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * People : ajax > obtenir le tableau de relation patient <-> patients
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//sortir le type de relation patient <-> patient
$data = new msData();
$typeID = $data->getTypeIDFromName('relationPatientPatient');
$options = $data->getSelectOptionValue(array($typeID));
$typeRelations=$options[$typeID];

$name2typeID = new msData();
$name2typeID = $name2typeID->getTypeIDsFromName(['relationID', 'relationPatientPatient', 'firstname', 'lastname', 'birthdate', 'birthname']);

if(isset($_POST['patientID'])) $patientID=$_POST['patientID']; elseif(isset($_GET['patientID'])) $patientID=$_GET['patientID'];

$data=[];
if($data = msSQL::sql2tab("select o.value as patientID, c.value as typeRelation, p.value as prenom, d.value as ddn, CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom
from objets_data as o
inner join objets_data as c on c.instance=o.id and c.typeID='".$name2typeID['relationPatientPatient']."'
left join objets_data as n on n.toID=o.value and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
left join objets_data as bn on bn.toID=o.value and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
left join objets_data as p on p.toID=o.value and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
left join objets_data as d on d.toID=o.value and d.typeID='".$name2typeID['birthdate']."' and d.outdated='' and d.deleted=''
where o.toID='".$patientID."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated=''
group by o.value, c.id, bn.id, n.id, p.id, d.id
order by typeRelation = 'MT' desc, nom asc")) {

  foreach($data as $k=>$v) {
    $data[$k]['typeRelationDisplay']=$v['typeRelation'];
  }


}

echo json_encode($data);
