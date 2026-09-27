//<script type="text/javascript">
/*
* pack_js_header.js (put this in same folder as parent html)
*
* v03-251006 -added colors to href 
* v02-251006 - added CSS code


*/


//</script>

// styles.js
const style = document.createElement('style');
style.type = 'text/css';
style.innerHTML = `



/*

	a {
	    font-size: 18px;
	    padding: 1px;
	    margin-bottom: 8px;
	    border: 1px solid #ccc;
	    border-radius: 1px;
	    background-color: #fff;
	}
*/
	a:hover {
	  background-color: lightgreen;
	}









	ol {
	  list-style-position: inside; /* ensures numbers are inside the list item */
	  /* list-style: none;  remove default markers */
  padding-left: 0;
	}

	li::before {
	  /*content: counters(list, ".") " "; /* or any custom marker */
	 /*  counter-increment: list;  */
	  font-weight: bold;
	  margin-right: 8px;
	}

  li {
    font-size: 18px;
    padding: 1px;
    margin-bottom: 8px;
    border: 1px solid #ccc;
    border-radius: 1px;
    background-color: #fff;
  }
	li:hover {
	  background-color: palegreen; /* change to your preferred hover color GreyLight: #f0f0f0 greens: palegreen ,mintcream,honeydew, */
	}

`;
document.head.appendChild(style);


