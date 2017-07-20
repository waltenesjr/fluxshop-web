<!DOCTYPE html>
<html>
<head>	
	<title>Redactor</title>
	<script type="text/javascript" src="redactor/api/jquery-1.7.min.js"></script>	
	<link rel="stylesheet" href="redactor/api/css/redactor.css" />
	<script src="redactor/api/redactor.js"></script>
	<script type="text/javascript">
	$(document).ready(
		function(){
			$('#redactor').redactor({ 
				focus: true,
				imageUpload: 'redactor/image_upload.php',
				fileUpload: 'redactor/file_upload.php',
				imageGetJson: 'redactor/json.php'				
				});
		}
	);
	</script>				
</head>
<body>
	<div id="page">
		<textarea id="redactor" name="content" style="height: 560px;">
		<p>Hello and Welcome</p>
		</textarea>
	</div>			
</body>
</html>