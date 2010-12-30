<?php
/*******************************************************************************/
/* AgataWEB
/* WEB for report generation
/* by Pablo Dall'Oglio - 2001 - 2006
/*******************************************************************************/
class AgataWEB {
	
    function AgataWEB() {
        // no class with that name
    	//include_once AGATA_PATH . '/web/class/sys.html/core/TMaster.php.php';
    }
    
    function readReports($user, $dir) {
		// no class with that name
        $filelist = getSimpleDirArray($dir, true);
        
        foreach($filelist as $file)
        {
            $report = $dir . bar . $file;
            $report = str_replace('/', bar, $report);
            $report = str_replace('\\', bar, $report);
            $filter->login  = $user;
            $filter->report = $report;
            // no class with that name
/*            if ($permissions->getObjects($filter) or $user == 'admin')
            {
                $return[] = $report;
            }*/
        }
        
        $return[] = $report;
        return $return;
    }
    
    
    function DirList($BrowseDir, $filter, $action, $agataConfig) {
        
    	if ($action == 'browse.php?goal=2') {
            $OutputDir = $agataConfig['general']['OutputDir'];
            $dir_len = strlen($OutputDir);
            
            if (substr($BrowseDir,0,$dir_len) != $OutputDir) {
                $BrowseDir = $OutputDir;
            }
        }
        else
        {
            $RptDir = $agataConfig['general']['RptDir'];
            $dir_len = strlen($RptDir);
            if (substr($BrowseDir,0,$dir_len) != $RptDir)
            {
                $BrowseDir = $RptDir;
            }    
        }
    
        $icofolder        = 'images/folder.png';
        $Images['sql']    = 'images/ico_sql.png';
        $Images['agt']    = 'images/ico_agt.png';
        $Images['html']   = 'images/ico_html.png';
        $Images['sxw']    = 'images/ico_sxw.png';
        $Images['txt']    = 'images/ico_txt.png';
        $Images['pdf']    = 'images/ico_pdf.png';
        $Images['ps']     = 'images/ico_ps.png';
        $Images['csv']    = 'images/ico_csv.png';
        $Images['xml']    = 'images/ico_xml.png';
        $Images['dia']    = 'images/ico_dia.png';
        $Images['generic']= 'images/generic.png';
    
        # Opens the Sql's Dir
        
        $filelist = getSimpleDirArray($BrowseDir, false);
        if ($filelist) {
            echo '<link href="site.css" rel="stylesheet" type="text/css">';
           
            echo "<form>";
            
            if ($action == 'browse.php?goal=1') {
                echo '<h1>Agata CoreReport :: ' . _a('Query Explorer') .'</h1>';
            } else {
                echo '<h1>Agata CoreReport :: ' . _a('Repository Explorer') . '</h1>';
            }
           
           echo '<ul id="nav">
               		<li>
               			<a href=browse.php?goal=1>' . _a('Query Explorer') . '</a>
               		</li>
               		<li>
               			<a href=browse.php?goal=2>' . _a('Repository Explorer') . '</a>
               		</li>
               	</ul>';        

           echo '<table class="orderable selectable">';  
            echo '
            	<thead>
            		<tr>
						<th scope="col" width="2%"></th>
						<th scope="col" width="2%"></th>
						<th scope="col" width="15%">Report Name</th>
						<th scope="col">Description</th>
					</tr>
            	</thead>
            
            ';
            
/*            $back = dir_back($BrowseDir);
            
            if ($back != $agataConfig['general']['AgataDir']) {
                echo '<tr>';
                echo '<td width=10% align=center>';
                echo "<a href=$action&BrowseDir=$back><img src=$icofolder border=0></a>";
                echo '</td>';
                echo '<td width=40% colspan=5 align=left>';
                echo "<a href=$action&BrowseDir=$back>..</a>";
                echo '</td>';
                echo '</tr>';
            }*/

            foreach ($filelist as $arquivo) {
                $arquivo = trim($arquivo);
                $path = $BrowseDir . bar . $arquivo;
                
                
                if (is_dir($path)) {
                    echo "<tr>";
                    echo '<td align=left>';
                    echo "<a href=$action&BrowseDir=$path><img src=$icofolder border=0></a>";
                    echo '</td>';
                    
                    echo '<td align=left valign=center>';
                    echo "<a href=$action&BrowseDir=$path>$arquivo</a>";
                    echo '</td>';
                    echo '</tr>';
                } else {
                    if (count($filter) == 1) {
                        $ok = ($filter) ? (in_array(substr($path,-strlen($filter[0])), $filter)) : true;
                    } else {
                        $ok0 = ($filter) ? (in_array(substr($path,-2), $filter)) : true;
                        $ok1 = ($filter) ? (in_array(substr($path,-3), $filter)) : true;
                        $ok2 = ($filter) ? (in_array(substr($path,-4), $filter)) : true;
                        $ok = ($ok0 or $ok1 or $ok2);
                    }
                    
                    if ($ok) {
                        $posicao = strpos($arquivo, '.');
                        $ext = substr($arquivo, $posicao+1);
                        $ico = $Images[$ext];
        
                        if (!$ico) {
                            $ico = $Images['generic'];
                        }
        
                        echo "<tr>";
    
                        if ($action == 'browse.php?goal=2') {
                        	echo '<td>';
                            echo "<a href=download.php?file=$path&download=$arquivo&type=$ext><img src=$ico border=0></a>";
                            echo '</td>';
                            echo '<td>';
                            echo "<a href=download.php?file=$path&download=$arquivo&type=$ext>$arquivo</a>";
                            echo '</td>';
                        } else {
                        	echo '<td>';
                            $lang = Trans::GetLanguage();
                            echo "<a href=agataweb.php?file=$path&lang=$lang&AgataDir=" . AgataDir . "><img src=$ico border=0></a>";
                            echo '</td>';
	                        //fastgenerate
	                        echo '<td>';
	                        if ($Report['Report']['Properties']['Layout'] AND $Report['Report']['Properties']['Format']) {
	                            echo "<a href='fastgenerate.php?file=$path'><img border=0 src=images/lightning.png></a>";
	                        }
                        	echo '</td>';
                        	//end fastgenerate
                            echo '<td>';
                            echo "<a href=agataweb.php?file=$path&lang=$lang&AgataDir=" . AgataDir . ">$arquivo</a>";
                            echo '</td>';
                        }
                        
                        if (substr($path,-3)=='agt') {
                            $Report = CoreReport::OpenReport($path);
                        }
                        
                        
                        
                       

                        
                        //filesize
					/*  echo '<td class=texto width=10% align=right>';
                        echo file_size($path);
                        echo '</td>';*/
                        
                        //date
                        /*echo '<td class=texto width=30% align=center>';
                        echo file_date($path);
                        echo '</td>';*/
                        
                        echo '<td>' . $Report['Report']['Properties']['Description'] . '</td>';
                        echo '</tr>';
                    }
                }
            }
            echo "</table>";
            echo "</form>";
        }
    }
}
?>