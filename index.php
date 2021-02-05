<?php
error_reporting(E_STRICT);

	$products = [
		["name" => "Sledgehammer", "price" => 125.75], 
		["name" => "Axe", "price" => 190.50], 
		["name" => "Bandsaw", "price" => 562.13], 
		["name" => "Chisel", "price" => 12.9], 
		["name" => "Hacksaw", "price" => 18.45],
	];

?>
<!DOCTYPE html>
<html>
<head>
	<title>Simple Shopping Cart</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
</head>
<body>
	<div class="col-sm-12 col-md-10 col-md-offset-1">
		<h4>Products</h4>
		<table class="table table-hover">
			<tr>
				<td><strong>Product name</strong></td>
				<td><strong>Price</strong></td>
				<td></td>
			</tr>
			<?php
				foreach($products as $product){
					echo '
					<tr>
						<td>' . $product['name'] . '</td>
						<td>$' . number_format($product['price'], 2, '.', '') . '</td>
						<td><a href="?action=add&name=' . $product['name'] . '">[+ Add to cart]</a></td>
					</tr>';
				}
			?>
		</table>
		<p>&nbsp;</p>
		<h4>Shopping Cart</h4>
		<?php
			require_once('cart.php');
			$cart = new Cart();
			$action = (isset($_GET['action'])) ? $_GET['action'] : '';
			$name = (isset($_GET['name'])) ? $_GET['name'] : 0;
			switch($action) {
				case 'add':
					foreach($products as $product) {
						if($product['name'] == $name) {
							$cart->add($name);
							break;
						}
					}
				break;
				case 'remove':
					$cart->remove($name);
				break;
				case 'empty':
					$cart->clear();
				break;
			}
			$items = $cart->getItems();
			if(!empty($items)){
				echo '
				<table class="table table-hover">
				<tr>
					<td><strong>Product name</strong></td>
					<td><strong>Price</strong></td>
					<td><strong>Quantity</strong></td>
					<td><strong>Total</strong></td>
					<td></td>
				</tr>';
				$total = 0;
				foreach($items as $name=>$qty) {
					foreach($products as $product) {
						if($product['name'] == $name)
							break;
					}
					echo '
					<tr>
						<td>' . $product['name'] . '</td>
						<td>$' . number_format($product['price'], 2, '.', '') . '</td>
						<td>' . $qty . '</td>
						<td>$' . number_format(($product['price'] * $qty), 2,'.', '' ). '</td>
						<td><a href="?action=remove&name=' . $name . '">[x Remove]</a></td>
					</tr>';
					$total += $product['price'] * $qty;
				}
				echo '
				<tr>
					<td><a href="?action=empty">[Empty Cart]</a></td>
					<td colspan="4" align="right"><strong>Overall Total: $' . number_format($total, 2, '.', '') . '</strong></td>
				</tr>
				</table>';
			}
			else{
				echo '<p style="color:#990000;">Your shopping cart is empty.</p>';
			}
		?>
	</div>
	<script>
        window.history.pushState({}, "/", "/");
    </script>
</body>
</html>