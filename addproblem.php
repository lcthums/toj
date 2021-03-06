<?php
	if (!isset($con)) {
		header('HTTP/1.0 404 Not Found', true, 404);
		include("../404.php");
		die();
	}

	if (!isset($_SESSION))
		session_start();
	if (!($_SESSION['logged'] == 1 && ($_SESSION['permission'] == 1 || $_SESSION['permission'] == 2)))
		header("Location: ?option=error");
?>

<html>
<head>
	<title>Create Problem</title>
</head>

<body>
<h2>Create Problem</h2>
<section>
<form id="addproblem" method="post" enctype="multipart/form-data">
	<label for="title">Title</label>
	<input id="title" name="title" type="text" placeholder="Hello World" class="form-control input-md" required>
	<label for="categoria">Type</label>
	<select id="categoria" name="categoria">
		<?php
			$result = pg_query($con, "SELECT id, name FROM problemType ORDER BY name");
			while ($row = pg_fetch_row($result)) {
				echo "<option value='".$row[0]."'>".$row[1]."</option>";
			}
		?>
	</select>
	<label for="descricao">Description:</label>
	<textarea name="descricao"></textarea>

	<label for="dificuldade">Level</label>
	<select id="dificuldade" name="dificuldade">
		<option selected value="1">1</option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
		<option value="6">6</option>
		<option value="7">7</option>
		<option value="8">8</option>
		<option value="9">9</option>
		<option value="10">10</option>
	</select>
	<label for="inputFile">Input File</label>
	<input type="file" name="inputFile" ID="inputFile" class="inputfile">
	<label for="outputFile">Output File</label>
	<input type="file" name="outputFile" ID="outputFile" class="inputfile">
	<button type="submit" form="addproblem" name="submit" value="submit">Create</button>
</form>

<legend>
	<?php
		if(isset($_POST['submit'])) { //check if form was submitted
			if ($_POST['title'] != "" && $_POST['categoria'] != "" && $_POST['descricao'] != "" && $_POST['dificuldade'] != "" && $_FILES["outputFile"]["size"] > 0 && $_FILES['inputFile']['size'] > 0) {
				$id = mt_rand(0, 2147483647);
				$ok = true;

				// output file
				$target = '/media/judge/output/'.$id.".output";
        		//echo "[DEBUG] output target = ".$target;

				if (move_uploaded_file($_FILES["outputFile"]["tmp_name"], $target))
				    echo " was upload sucessfully.<br>";
				else {
					$ok = false;
					echo "Sorry, there was an error uploading your file. Please try again.<br>";
				}

				//input file
				$target = '/media/judge/input/'.$id.'.input';
        		//echo "[DEBUG] input target = ".$target; 

				if (move_uploaded_file($_FILES['inputFile']['tmp_name'], $target))
				    echo " was upload sucessfully.<br>";
				else {
					$ok = false;
					echo "Sorry, there was an error uploading your file. Please try again.<br>";
				}

				$query = 
					"INSERT INTO problems VALUES (".$id.", '".$_POST['title']."', ".$_POST['dificuldade'].", '".$_POST['descricao']."', ".$_POST['categoria'].", ".$_SESSION['id'].");";
				
				// TODO: SQL Injection protection
				$result = pg_query($con, $query);

				if ($result && $ok) {
					$dir = 'problems';

					 // create new directory with 744 permissions if it does not exist yet
					 // owner will be the user/group the PHP script is run under
					 if (!file_exists($dir)) {
					     $oldmask = umask(0);  // helpful when used in linux server  
					     mkdir ($dir, 0777);
					 }

					// creates the html file for the problem
					$file_name = $dir."/".$id.".html";
					$file = fopen($file_name, "w");
					$select = "SELECT  users.name, users.email FROM users WHERE users.id = ".$_SESSION['id'];
					$result = pg_query($con, $select);
					if (!$result) {
						echo "Something bad happened. Please try again later.<br>";
						exit(1);
					}
					$row  = pg_fetch_row($result);
					$content = "<section><h2>".$_POST['title']."</h2><br><small style='float: right;'><a href = '?option=profile&id=".$_SESSION['id']."'>".$row[0].", ".$row[1]."</a></small><br>".$_POST['descricao']."</section>";
					fwrite($file, $content);
					echo "<a href='?problem=".$id."'>Done. Click here to see your new problem.</a>";
				}
				else {
					echo "There was a problem adding the problem to the database, please try again later.<br>";
					exit(1);
				}
			}
			else {
				echo "Please fill all required fields correctly.<br>";
				exit(1);
			}
			echo "</legend></section><footer>$query<br>$select</footer>";
		}
	?>

</body>
</html>
