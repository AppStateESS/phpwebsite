<html>
<head>
<title>{TITLE}</title>
<style type="text/css">
body {
 background-color : #A6B8CC;
 font-size : 100%;
 font-family: Arial, Helvetica, sans-serif;
 color : #0E1662;
}

.long input{
 width : 100%;
}

#frame {
  margin-top : 20px;
  padding : 10px;
  margin-left : 50px;
  margin-right : 50px;
  background-color : #DCDDE2;

}

.user-error {
  background-color : red;
  color : white;
  font-weight : bold;
}

#title {
 font-size : 150%;
 font-weight : bold;
 text-align : center;
 background-color : #0E1662;
 padding : 5px;
 color : white;	
}

#main {
 margin : 10px;
}

#config-form {
 margin : 3px;
 background-color : white;
 padding : 2px;
}

#config-form div.config-item {
 margin : 10px;
 background-color : #f2f2f2;
 padding : 10px;

}

#config-form div.label {
 font-weight : bold;
}

#config-form div.definition {
 font-size : 90%;	
}

#config-form div.form-item {
 margin-top : 5px;
}


</style>
</head>
<body>
<div id="frame">
  <div id="title">{TITLE}</div>
  <div id="main">{MAIN_CONTENT}</div>
</div>
</body>
</html>
