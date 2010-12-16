<?php
/***********************************************************/
/* Class defining the filetree box
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class FileTree extends GtkCTree
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function FileTree()
    {
        GtkCTree::GtkCTree(1, 0, array(''));
        GtkCTree::set_selection_mode(GTK_SELECTION_SINGLE);
        GtkCTree::set_line_style(GTK_CTREE_LINES_SOLID);
        GtkCTree::column_titles_hide();
        GtkCTree::connect_object('tree-select-row', array(&$this, 'Abrir'));
        $this->BotaoOk = new GtkButton;
        $this->SetShowExtension(true);
    }

    function SetShowExtension($flag)
    {
        $this->ShowExtension = $flag;
    }

    /***********************************************************/
    /* Opens the selected node
    /***********************************************************/
    function Abrir()
    {
        $selecao = $this->selection[0];
        $Arquivo = GtkCTree::node_get_row_data($selecao);
        if (is_dir($Arquivo) && (!$this->opened[$Arquivo]))
        {
            $this->opened[$Arquivo] = true;
            $this->AbreDiretorio($Arquivo, $selecao, null);
            GtkCTree::expand($selecao);
        }
        elseif (is_dir($Arquivo) && ($this->opened[$Arquivo]))
        {
            GtkCTree::expand($selecao);
        }
        elseif ($Arquivo=='home')
        {
            GtkCTree::expand($selecao);
        }
        else
        {
            $botao = $this->BotaoOk;
            $botao->clicked();
        }
    }

    /***********************************************************/
    /* Removes a node and file
    /***********************************************************/
    function Apagar()
    {
        $selecao = $this->selection[0];
        $Arquivo = GtkCTree::node_get_row_data($selecao);
        if (is_dir($Arquivo))
        {
            include_once 'Dialog.php';
            new Dialog('Sem a��o');
        }
        else
        {
            GtkCTree::remove_node($selecao);
            unlink($Arquivo);
        }
    }

    /***********************************************************/
    /* Difine allowed extensions
    /***********************************************************/
    function DefineExtensions($extensions)
    {
        $this->extensions = $extensions;
    }

    /***********************************************************/
    /* Opens a directory, loads it into the tree
    /***********************************************************/
    function AbreDiretorio($Diretorio, $NodoRaiz)
    {
        global $Pixmaps;

        if (!$NodoRaiz)
        {
            $entries = $this->entries;
            $entries[] = $Diretorio;
            $this->entries = $entries;
        }
        $extensions = $this->extensions;
        
        $ico = $Pixmaps['home'];
        
        $dir = @opendir($Diretorio);
        if (!$dir)
        {
            return;
        }

        while (($arquivo = readdir($dir)) !== false)
        {
            $Caminho = "$Diretorio" . bar . "$arquivo";
            
            if (is_dir($Caminho))
                $arquivo = ' ' . $arquivo;
            
            if (substr(trim($arquivo),0,1) != '.')
            {
                if (($extensions) && (is_file($Caminho)))
                {
                    foreach ($extensions as $ext)
                    {
                        if (strstr($arquivo, $ext))
                        {
                            $filelist[] = $arquivo;
                        }
                    }
                }
                else
                {
                    $filelist[] = $arquivo;
                }
            }
        }
        
        if ($filelist)
        {
            sort($filelist);
            foreach ($filelist as $arquivo)
            {
                $arquivo = trim($arquivo);
                $Caminho = "$Diretorio" . bar . "$arquivo";
                if ($arquivo == '.' || $arquivo == '..')
                continue;
                
                if (is_dir($Caminho))
                {
                    $ico1 = $Pixmaps['folder1'];
                    $ico2 = $Pixmaps['folder2'];
                    $NodoPasta = GtkCTree::insert_node(
                        $NodoRaiz, null, array($arquivo), 5,
                        $ico1[0], $ico1[1], $ico2[0], $ico2[1], false, false);
                    
                    //$this->AbreDiretorio($Caminho, $NodoPasta);
                    GtkCTree::node_set_row_data($NodoPasta, $Caminho);
                }
                else
                {
                    $posicao = explode('.', $arquivo);
                    $node_text = '';

                    if ($this->ShowExtension)
                    {
                        $node_text = $arquivo;
                    }
                    else
                    {
                        for($n = 0; $n<count($posicao)-1; $n++)
                        {
                            $node_text .= $node_text ? '.' . $posicao[$n] : $posicao[$n];
                        }
                    }

                    $ext = $posicao[count($posicao)-1];
					$ico = $Pixmaps[$ext];
                    if (!$ico)
                        $ico = $Pixmaps['generic'];

                    $NodoFilho = GtkCTree::insert_node(
                        $NodoRaiz, null , array($node_text), 5,
                        $ico[0], $ico[1], $ico[0], $ico[1], true, false);
                    
                    GtkCTree::node_set_row_data($NodoFilho, $Caminho);
                }
            }
        }
        GtkCTree::thaw();
    }

    /***********************************************************/
    /* Reloads the tree
    /***********************************************************/
    function ReadAgain()
    {
        $this->opened = null;
        GtkCTree::clear();
        GtkCTree::thaw();
        $entries = $this->entries;
        $this->entries = null;
        
        if ($entries)
        {
            foreach($entries as $entry)
            {
                $this->AbreDiretorio($entry, null);
            }
        }
    }

    /***********************************************************/
    /* Clear all the contents
    /***********************************************************/
    function MyClear()
    {
        GtkCTree::clear();
        GtkCTree::thaw();
        $this->entries = null;
    }
    
    function AddHome($Home, $pixmap = null)
    {
        global $Pixmaps;
		$ico = ($pixmap ? $pixmap : $Pixmaps['home']);

        $node = GtkCTree::insert_node(
            $NodoNull, null, array(':: ' . $Home), 5,
            $ico[0], $ico[1], $ico[0], $ico[1], false, false);
        GtkCTree::node_set_row_data($node, 'home');
        return $node;
    }
}
?>