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
 * Config > action : sauver la configuration d'un agenda
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

//utilisateurs pouvant avoir un agenda
$agendaUsers= new msPeople();
$autorisedUsers=$agendaUsers->getUsersListForService('administratifPeutAvoirAgenda');

//construction du répertoire
msTools::checkAndBuildTargetDir($p['config']['webDirectory'].'agendasConfigurations/');


if($_POST['userID']>0 and in_array($_POST['userID'], array_keys($autorisedUsers))) {
    file_put_contents('../config/configTypesRdv'.$_POST['userID'].'.yml', $_POST['configTypesRdv']);

    if(empty($_POST['configTypesRdv']))
        unlink('../config/configTypesRdv'.$_POST['userID'].'.yml');
    if(empty($_POST['configAgendaAd'])) {
        unlink($p['config']['webDirectory'].'agendasConfigurations/configAgenda'.$_POST['userID'].'_ad.js');
    } else {
        file_put_contents($p['config']['webDirectory'].'agendasConfigurations/configAgenda'.$_POST['userID'].'_ad.js', $_POST['configAgendaAd']);
    }
    if(empty($_POST['configAgenda'])) {
        unlink('../config/configAgenda'.$_POST['userID'].'.yml');
        unlink($p['config']['webDirectory'].'agendasConfigurations/configAgenda'.$_POST['userID'].'.js');
    } else {
        file_put_contents('../config/configAgenda'.$_POST['userID'].'.yml', $_POST['configAgenda']);
        $params=Spyc::YAMLLoad('../config/configAgenda'.$_POST['userID'].'.yml');
  
        $js=array();
        $js[]="var businessHours = [\n";
        $hiddenDays=array();
        $d=1;
        foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $day) {
            $js[]="  {\n";
            $js[]="    dow: [".$d."],\n";
            $js[]="    start: '".$params[$day]['minTime'].":00',\n";
            $js[]="    end: '".$params[$day]['maxTime'].":00',\n";
            $js[]="  },\n";
            if (!$params[$day]['visible']) {
                $hiddenDays[]=$d;
            }
            $d++;
            $d%=7;
        }
        $js[]="];\n";

        $js[]="var hiddenDays = [".implode(', ', $hiddenDays)."];\n";

        $js[]="var eventSources = [{\n";
        $js[]="    url: urlBase + '/agenda/".$_POST['userID']."/ajax/getEvents/'\n";
        $js[]="  },\n";
        $js[]="  {\n";
        $js[]="    events:[\n";
        $d=1;
        foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $day) {
            if ($params[$day]['pauseStart'] != $params[$day]['pauseStop'] and !in_array($d, $hiddenDays)) {
                $js[]="      {\n";
                $js[]="        start: '".$params[$day]['pauseStart'].":00',\n";
                $js[]="        end: '".$params[$day]['pauseEnd'].":00',\n";
                $js[]="        dow: [".$d."],\n";
                $js[]="        rendering: 'background',\n";
                $js[]="        className: 'fc-nonbusiness'\n";
                $js[]="      },\n";
            }
            $d++;
            $d%=7;
        }
        $js[]="    ]\n";
        $js[]="  }\n";
        $js[]="];\n";

        $js[]="var minTime = '".$params['minTime'].":00';\n";
        $js[]="var maxTime = '".$params['maxTime'].":00';\n";
        $js[]="var slotDuration = '".$params['slotDuration'].":00';\n";
        file_put_contents($p['config']['webDirectory'].'agendasConfigurations/configAgenda'.$_POST['userID'].'.js', $js);
    }
}

msTools::redirection('/configuration/agenda/'.$_POST['userID'].'/');
