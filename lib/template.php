<?
class template 
{ 
    var $vars = array(); 

    function assign($var_array) 
    { 
        if (!is_array($var_array))  { 
			return;
        } 
        $this->vars = $var_array; 
    } 

    function render($file) 
    { 
		$file = APP_ROOT_DIR . "/$file";
        if (!file_exists($file))  { 
			echo "Template Error: Unable to open file $file<br>";
			//echo "In folder: " . getcwd();
            return;
        } 
        $file_content = file_get_contents($file); 
         
        foreach ($this->vars as $var => $content) 
        { 
            $file_content = str_replace('{' . $var . '}', (string)$content, $file_content); 
        } 
        return $file_content; 
    } 

    function display($file) 
    { 
        echo $this->render($file); 
    } 
} 
?>