<?php
           # Include AgataAPI class
            include_once '/agata/agata/core/AgataAPI.php';

            # Instantiate AgataAPI
            $api = new AgataAPI;
            $api->setLanguage('en'); //'en', 'pt', 'es', 'de', 'fr', 'it', 'se'

            $api->setOutputPath('/tmp/withoutSql.sxw');

            # Set main data
            $data = array( array('Jamiel Spezia', 'Engenharia da Computa��o'),
                           array('William Prigol Lopes', 'An�lise de Sistemas'),
                           array('Rafael Luis Spengler', 'An�lise de Sistemas'),
                           array('Daniel Afonso Heisler', 'Engenharia da Computa��o')
                         );

            # Set sub data
            #  $subData[level][register_father]
            # referring Jamiel
            $subData[0][1] = array( array('Compiladores', '203-7'),
                                    array('Sistema de banco de dados', '204-7'),
                                    array('Mec�nica dos s�lidos', '303-8')
                                  );
            # referring William
            $subData[0][2] = array( array('Compiladores', '203-7'),
                                    array('Banco de dados', '205-7'),
                                    array('Administra��o', '203-8')
                                  );
            # referring Rafael
            $subData[0][3] = array( array('Banco de dados', '205-7'),
                                    array('Administra��o', '203-8')
                                  );
            # referring Daniel
            $subData[0][4] = array( array('Compiladores', '203-7')
                                  );


            $api->setDataArray($data);
            $api->setSubDataArray($subData);

            $ok = $api->parseOpenOffice('/agata/resources/withoutSql.sxw');
            if (!$ok)
            {
                 echo $api->getError();
            }
            else
            {
                //$api->fileDialog();
            }
?>
