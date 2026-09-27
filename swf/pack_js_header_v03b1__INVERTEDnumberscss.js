//<script type="text/javascript">
/*
* pack_js_header.js (put this in same folder as parent html)
*
*
* v03b1__251009 INVERTEDnumberscss
* v03b-251006b - Added inverted numbering
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








/* Reset list styles and prepare for custom counters */
ol {
  counter-reset: list;
  list-style: none; /* Remove default list markers */
  padding-left: 0;
}



/* Style the counter with inverted background and text color */
li::before {
  content: counters(list, ".") " ";
  display: inline-block;
  padding: 2px 4px; /* Adjust padding as needed */
  background-color: grey; /* Inverted background color */
  color: white; /* Text (ink) color */
  font-weight: bold;
  border-radius: 4px; /* Optional: rounded corners */
  margin-right: 8px; /* Space between counter and list item text */
}

  li {

      counter-increment: list;
    position: relative; /* Optional, for posit */

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


