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
 * Requêtes AJAX > autocomplete des forms, version simple 
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 $type=$match['params']['type'];
 $dataset=$match['params']['dataset'];

 $dataset2database=array(
     'data_types'=>'objets_data'
 );

 $database=$dataset2database[$dataset];

 if (isset($match['params']['setTypes'])) {
     $searchTypes=explode(':', $match['params']['setTypes']);
 } else {
     $searchTypes[]=$type;
 }

 $data=msSQL::sql2tab("select distinct(value) from ".$database." where typeID in ('".implode("','", $searchTypes)."') and value like '".msSQL::cleanVar($_GET['term'])."%' ");

 echo json_encode($data);
