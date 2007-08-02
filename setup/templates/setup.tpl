<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
{META}
<meta content="text/html; charset=UTF-8"  http-equiv="Content-Type" />

<title>{TITLE}</title>
<style type="text/css">
body {
 background-color : #ADB583;
 font-size : 100%;
 font-family: Arial, Helvetica, sans-serif;
 color : #0E1662;
}

.long input{
 width : 100%;
}

#frame {
  margin-top : 20px;
  margin-left : 50px;
  margin-right : 50px;
  background-color : #DFE3BA;

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
 background-color : #A03E19;
 padding : 5px;
 color : white;	
}

#main {
 padding : 10px;
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
a:link, a:visited {
    text-decoration : none;
    background-color : inherit;
    color: #A03E19;
}

a:hover, a:active {
    text-decoration : underline;
    background-color : inherit;
    color: #000;
}

div.error {
  background-color : red;
  color : white;
  font-weight : bold;
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
