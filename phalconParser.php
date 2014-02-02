<?php 

	require_once('simple_html_dom.php');
	
	define('PHALCON_API_FOLDER', 'phalcon-php-framework-documentation-latest/api');
	define('HTML_DESTINATION_FOLDER', 'phalconphp.docset/Contents/Resources/Documents/api');
	define('SQLITE_FILE', 'phalconphp.docset/Contents/Resources/docSet.dsidx');
	
	define('CLASSN', 'Class');
	define('CONSTANT', 'Constant');
	define('METHOD', 'Method');
	
	$excludedFiles = array('.', '..', 'index.html', '.DS_Store');
	
	if ($handle = opendir(PHALCON_API_FOLDER)) {	
		while (false !== ($fileName = readdir($handle))) {
			if (!in_array($fileName, $excludedFiles)) {
				parseFile($fileName);
			}
		}				
		closedir($handle);
	}
	
	/**
	* parseFile parses the HTML documentation file
	* @param string $pFile fileName to be processed
	*/
	
	function parseFile($pFile)
	{
		echo '> Scanning File: ' . $pFile . PHP_EOL;
		
		$html = file_get_html(PHALCON_API_FOLDER . '/' . $pFile);
						
		if ($html) {
			// Open the SQLite connection
			$sqlite = new PDO('sqlite:' . SQLITE_FILE);
			$sqlite->setAttribute(	PDO::ATTR_ERRMODE,
									PDO::ERRMODE_EXCEPTION);
			// Search the Class
			$class = $html->find('h1 strong', 0);
			searchFor($sqlite, array($class), CLASSN, $pFile);
			
			// Search the Constants
			$constants	= $html->find('div[id=constants] p strong');
			if (count($constants) > 0) {
				searchFor($sqlite, $constants, CONSTANT, $pFile);
			}
			unset($constants);
			
			// Search the Methods			
			$methods	= $html->find('div[id=methods] p strong');
			if (count($methods) > 0) {
				searchFor($sqlite, $methods, METHOD, $pFile);
			}
			unset($methods);
			
			// Rewrite the HTML documentation 
			rewriteHtml($html, $pFile);
			
			$sqlite = null;	// Close the SQLite connection
		}
	}
	
	/**
	 * searchFor 
	 * @param object $pSqlite SQLite handler
	 * @param array $pData name of the class, methods, constants
	 * @param string $pType CLASS, CONSTANT, METHOD
	 * @param string $pFile The HTML file which will be used by Dash to display the documentation
	 */
	function searchFor($pSqlite, $pData, $pType, $pFile)
	{
		echo 'Number of ' . $pType . ' found: ' . count($pData) . PHP_EOL;
		$items = array();
		
		foreach ($pData as $item) {
			array_push($items, $item->plaintext);
			$item->outertext = '<a name="//apple_ref/cpp/' . $pType . '/'. $item->plaintext .'" class="dashAnchor">'.$item->innertext.'</a>';
		}
		
		insert($pSqlite, $items, $pType, $pFile);
	}
	
	/**
	 * insert inserts into the Dash SQLite database the required data
	 * @param object $pSqlite SQLite handler
	 * @param array $pData name of the class, methods, constants
	 * @param string $pType CLASS, CONSTANT, METHOD
	 * @param string $pFile destination file where the new documentation must be written
	 */
	
	function insert($pSqlite, $pData, $pType, $pFile)
	{
	
		$insert = 'INSERT OR IGNORE INTO searchIndex (name, type, path) VALUES (:name, :type, :path)';
		$stmt = $pSqlite->prepare($insert);
		
		foreach ($pData as $data) {
			$stmt->bindValue(':name', $data);
			$stmt->bindValue(':type', $pType);
			$stmt->bindValue(':path', 'api/'.$pFile);
			$stmt->execute();
		}		
	}
	
	/**
	 * rewriteHtml removes menu, first colum of array from the Phalcon documentation in order to have something more readable in Dash viewer
	 * @param object $pHtml the HTML DOM
	 * @param string $pFilename destination file where the new documentation must be written
	 */
	
	function rewriteHtml($pHtml, $pFileName)
	{
	
		$menuBar = $pHtml->find('div[class=size-wrap]', 0);
		$menuBar->innertext = '
			<div class="header clear-fix">
				<a class="header-logo" href="http://phalconphp.com"><span class="logo-text">Phalcon</span></a>
			</div>' . PHP_EOL;
			
		$docTitle = $pHtml->find('div[class=header-line]', 0);
		$docTitle->outertext = '';
		
		$docRelated = $pHtml->find('div[class=related]', 0);
		$docRelated->outertext = '';
		
		$tdFirstCol = $pHtml->find('td[class=primary-box]', 0);
		$tdFirstCol->outertext = '';
		$tdFirstCol->width = '0px';
		
		file_put_contents(HTML_DESTINATION_FOLDER . '/' . $pFileName, $pHtml);	
	}
	
?>