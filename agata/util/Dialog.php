<?php

/**
 * Dialog class, shows messages to the users
 * by Pablo Dall'Oglio 2001-2006
 * Adjusted by Eduardo Bonfandini 29/09/2008
 */
class Dialog {

    /**
     * Constructor Method
     * @param $mensagem the message to output in screen
     * @param $erro if is erro or not
     * @param $kill if is to kill aplication or not
     * @param #submessage is a internal message (or submessage) to show in dialog
     */
    function Dialog($mensagem, $erro = true, $kill = false, $submessage = null) {
        global $agataConfig;
        if ($agataConfig['general']['ShowMessage']) {
            if (!is_array($mensagem)) {
                $mensagem = ereg_replace("\r", ' ', $mensagem);
                $mensagem = ereg_replace("\n", ' ', $mensagem);
                if (strlen($mensagem) > 40) {
                    $msgs = explode(' ', $mensagem);
                    $n = 0;
                    foreach ($msgs as $msg) {
                        $result[$n] .= ' ' . $msg;
                        if (strlen($result[$n]) > 40)
                            $n++;
                    }
                    $mensagem = $result;
                }
            }
            $this->ShowMessage($mensagem, $erro, $kill, $submessage);
        }
    }

    /**
     * Creates the Message Window
     * function called when you put new Dialog...
     */
    function ShowMessage($labels, $erro, $kill, $submessage) {
        if (is_array($labels)) {
            echo '<b>' . _a('Message') . ': </b>' . implode(' ', $labels) . '<br>';
        } else {
            echo '<b>' . _a('Message') . ': </b>' . $labels . '<br>';
        }
    }

}

?>