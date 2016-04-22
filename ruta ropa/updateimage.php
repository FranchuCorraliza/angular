<?php 

if($_GET):
	
		$proxy = new SoapClient('http://www.elitestore.es/api/soap/?wsdl=1');
		$user = "cuartafoto";
		$password = "29843cd7dbfd10cf832792f9b2c2a1cb";

		$sessionId = $proxy->login($user, $password);

	if($_GET['sku']!=""):	

		$filters = array(
		    'sku' => array('like'=> $_GET['sku']),
		    'type' => array('like'=>'configurable')
		);

		$products = $proxy->call($sessionId, 'product.list', array($filters));

		//var_dump($products);
		if(count($products)==0):
			echo "<span style='color:red;'>el producto no existe</span>";
		else:
		$productSku=$products[0]["sku"];

		/*foreach ($products as $productSkuSearch):
			if($productSkuSearch["type"]=="configurable")
				$productSku = $productSkuSearch["sku"];
		endforeach;*/

		$result = $proxy->call($sessionId, 'catalog_product.info', $productSku);
		$ruta = $result["rutaimagen"];

		$rutaimagen = "http://www.elitestore.es" . substr($ruta,0,-5) . "3.jpg";

		$file_headers = @get_headers($rutaimagen);


				if($file_headers[0]!='HTTP/1.1 404 Not Found')://file_exists($rutaimagen)):

				//insrtar imagen en producto como cuarta foto
				//echo file_get_contents($rutaimagen);
				$newImage = array(
				    'file' => array(
				        'name' => 'file_name',
				        'content' => base64_encode(file_get_contents($rutaimagen)),
				        'mime'    => 'image/jpeg'
				    ),
				    //'label'    => '',
				    'position' => 3,
				    //'types'    => array('small_image', 'thumbnail', 'image'),
				    'exclude'  => 0,
				    'defaultimg'  => 0
				);

				$imageFilename = $proxy->call($sessionId, 'product_media.create', array($productSku, $newImage));

				$result = $proxy->call($sessionId, 'catalog_product.update', array($productSku, array(
					'has_four_images' => 1
				)));
				echo "imagen subida correctamente<br/>sku: " . $productSku . "</br> imagen:</br> <img src='" . $rutaimagen . "' width='200px' />";
				//var_dump($imageFilename);
				/*
				// Newly created image file
				var_dump($proxy->call($sessionId, 'product_media.list', 'Sku'));

				$proxy->call($sessionId, 'product_media.update', array(
				    'Sku',
				    $imageFilename,
				    array('position' => 2, 'types' => array('image') // Lets do it main image for product )
				));

				// Updated image file
				var_dump($proxy->call($sessionId, 'product_media.list', 'Sku'));

				// Remove image file
				$proxy->call($sessionId, 'product_media.remove', array('Sku', $imageFilename));

				// Images without our file
				var_dump($proxy->call($sessionId, 'product_media.list', 'Sku'));*/
				else:
					echo "</br><span style='color:red;'>la ruta no existe </br>ruta: " . $rutaimagen."</span></br>";
				endif; 
			endif;
	elseif($_GET['nombre']!=""):
		$filters = array(
		    'name' => array('like'=> "%" . $_GET['nombre'] . "%"),
		    'type' => array('like'=>'configurable')
		);

		$products = $proxy->call($sessionId, 'product.list', array($filters));
		if(count($products)==0):
			echo "<span style='color:red;'>no hay ningún producto con el nombre " . $_GET['nombre'] . " </span>";
		elseif(count($products)==1):
			header("Location: ./?sku=" . $products[0]['sku']); 
		else:
			echo "<ul>";
		$manufacturers = $proxy->call($sessionId, 'product_attribute.options', array(81));
			foreach ($products as $product) :
				$result = $proxy->call($sessionId, 'catalog_product.info', $product['product_id']);
				foreach ($manufacturers as $manufaturer):
					if($manufaturer["value"]==$result['manufacturer'])
						$marca=$manufaturer["label"];
				endforeach;
				?>
				<li>
				<a href="/?sku=<?php echo $product['sku'];?>">nombre : <?php echo $product['name'];?> ---- marca : <?php echo $marca;?> ----- sku : <?php echo $product['sku'];?></a>
				</li>
			<?php
			endforeach;
			echo "</ul>";
		endif;
	elseif($_GET['order']!=""):
		$result = $proxy->call($sessionId, 'catalog_product.update', array($_GET['order'], array(
					'no_shop_button' => 1
				)));
	elseif($_GET['stock']!=""):
		$result = $proxy->call($sessionId, 'cataloginventory_stock_item.list', $_GET['stock']);
		foreach ($result as $productStock):
			echo "<br/>el producto " . $productStock["sku"];
			if ($productStock["is_in_stock"]=="1")
				echo " tiene stock<br/>";
			else
				echo " no tiene stock<br/>";
		endforeach;
	endif;
endif;
?>
<form>
nombre:</br>
	<input type="text" name="nombre"/>
</br>sku: </br>
	<input type="text" name="sku"/></br>
order:</br>
	<input type="text" name="order"/></br>
stock:</br>
	<input type="text" name="stock"/></br>

	<input type="submit"/>
</form>
<?php


?>