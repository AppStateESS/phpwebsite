<style>

#menu-links {
margin : 0;
padding : 0;
}

#menu-links ul {
margin-left : 0;
padding-left : 0;
}

#menu-links ul > li {
padding : 4px 0 4px 1em;
}

#menu-links li ul {
display: none;
}

#menu-links li:hover > ul {
 display: block;
} 

#menu-links li {

list-style : none;
background-color : #DEE2BD;

}

#menu-links li {
border-top : 1px white solid;
border-bottom : 1px white solid;
}

#menu-links li li {
border : none;
}

</style>

<div class="box">
  <h1 class="box-title">{TITLE}</h1>
  <div id="menu-links"><ul>{LINKS}</ul></div>
  <div class="align-center smaller">{ADD_LINK}</div>
</div>
