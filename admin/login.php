<?php require("login.class.php");
var_dump(file_exists('login.json'));
var_dump(file_get_contents('login.json'));
?>

<?php
if (isset($_POST['submit'])) {
	$stored = json_decode(file_get_contents('login.json'), true);
	echo '<pre>';
	echo 'Utilisateur saisi : ' . htmlspecialchars($_POST['username']) . "\n";
	echo 'Fichier login.json lu : ' . (is_array($stored) ? 'oui' : 'non') . "\n";
	foreach ($stored as $u) {
		echo 'Username stocké : ' . $u['username'] . "\n";
		echo 'Match username : ' . ($u['username'] == $_POST['username'] ? 'oui' : 'non') . "\n";
		echo 'password_verify : ' . (password_verify($_POST['password'], $u['password']) ? 'oui' : 'non') . "\n";
	}
	echo '</pre>';
	$user = new LoginUser($_POST['username'], $_POST['password']);

}
var_dump($_POST);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="styles.css">
	<link rel="stylesheet" href="css/login.css">
	<title>Log in form</title>
</head>

<body>

	<form action="" method="post" enctype="multipart/form-data" autocomplete="off">
		<h2>Login form</h2>
		<h4>Both fields are <span>required</span></h4>

		<label>Username</label>
		<input type="text" name="username">

		<label>Password</label>
		<input type="text" name="password">

		<button type="submit" name="submit">Log in</button>

		<p class="error"><?php echo @$user->error ?></p>
		<p class="success"><?php echo @$user->success ?></p>
	</form>

</body>

</html>