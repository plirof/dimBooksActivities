//<script type="text/javascript">
/*
* pack_js_footer  (put this in same folder as parent html)
*
*
* Changes:
* v251123 - li_multicolor_set(4)
* v251118 - Chrome 140+ : Added modifyLinks(); call inside probeserver to modify ruffle links in each refresh
* v251110b - function li_multicolor_set() { alternating colors in <li> elements
* v251010b - html from probeserver hash is now inverted (line 145) and Trimmed the first letter of the name
* v250203 - Change 'time' to 'timer' (to avoid problems with URLs in pack_url_param.html ). url_time_param=location.search.substring(1).indexOf("timer"); 
* v250128 - changed currentHref.includes('opengame_ruffle') from currentHref.includes('ruffle') because it ignored many games with ok_ruffle in theirname
* v250120  - Added breakrefreshALL for all breaks
* v241213c - Do not clear div probeserver when connection is lost.
* v241127c - swfChangeHrefToRuffle Dynamically Change URLs to Ruffle when we have newer chrome. Fireforx+Chrome , ignore ruffle versions
* v241010c - Refeshtime : added olohmero :breakrefresh7ol 13:20,14:"00",15:"00"
* v240117b - refreshtimeXXYY : Now I can put refreshtime09:15 to reload at 09:15 ,fixed to "09","47"
* v240110 - addHashLabels: Add hash prefix in all links loaded inside probeserver (for olohmero mainly)
* v231031 - breakrefresh# fix error in IF checks
* v231011 - breakrefresh# reload at specific times start
* v201025b - eval (pack_refresh_browser.txt) execute commands dynamic (requires probeserver activated) (not implemented yet)
* v201020c -norightclick -disable right click : eg http://192.168.1.200/tinymce_class/tinymce_template_form.html?file=temp_test01&norightclick
* v190411 -noopengame (initial ver) (removes opengame -in case we run a plain Http server with no PHP)
* v190410 -opengame (initial ver)
* v190404 -showdiv
* v190402 -all scripts are now in footer
* 
* example : index.html?showdiv5\&timer3\&probeserver , 
*/

    var first_click=true;
    var addhashlabellinks=true; // allows auto hash numbering (maybe not used)
    var url_time_param=location.search.substring(1).indexOf("timer"); //250203 changed time->timer
    var swfChangeHrefToRuffle=true; //Change URLs to Ruffle opengame_ruffle.php when Chrome>ver70
    // (((((((((((((((((((  preset timers (((((((((((((((((((
    var additional_wait_time=0;
    var url_timer2=location.search.substring(1).indexOf("timer2");
    var url_timer3=location.search.substring(1).indexOf("timer3");
    var url_timer4=location.search.substring(1).indexOf("timer4");
    var url_timer5=location.search.substring(1).indexOf("timer5");
    var url_timer6=location.search.substring(1).indexOf("timer6");
    var url_timer7=location.search.substring(1).indexOf("timer7");

    if (url_timer2!==-1)additional_wait_time= -3;
    if (url_timer3!==-1)additional_wait_time= -2;
    if (url_timer4!==-1)additional_wait_time= -1;
    if (url_timer5!==-1)additional_wait_time= 0;
    if (url_timer6!==-1)additional_wait_time= 1;
    if (url_timer7!==-1)additional_wait_time= 2;
    var additional_wait_minutes=additional_wait_time*1000*60;
    // ))))))))))))))))  preset timers )))))))))))))))))))))))

    // (((((((((((((((((((  option to show only specific DIV (((((((((((((((((((
    var url_showdiv_param=location.search.substring(1).indexOf("showdiv");// != to -1 if we have this param
    var url_showdiv1=location.search.substring(1).indexOf("showdiv1");
    var url_showdiv2=location.search.substring(1).indexOf("showdiv2");
    var url_showdiv3=location.search.substring(1).indexOf("showdiv3");
    var url_showdiv4=location.search.substring(1).indexOf("showdiv4");
    var url_showdiv5=location.search.substring(1).indexOf("showdiv5");
    var url_showdiv6=location.search.substring(1).indexOf("showdiv6");
    var url_showdiv7=location.search.substring(1).indexOf("showdiv7");
    // ))))))))))))))))  option to show only specific DIV  )))))))))))))))))))))))

    var url_opengame=location.search.substring(1).indexOf("opengame"); //check if we want opengame as prefix to all urls
    var url_noopengame=location.search.substring(1).indexOf("noopengame"); //check if we DO NOT want opengame as prefix to all urls (for PHPless web servers)


//if we find a parameter that contains word "time" (eg packA01.html?timer) then we hide all the other links
function init_links(){

    if((url_showdiv_param!==-1 ) && (url_time_param==-1 ) ){  //disable show_url IF we have set timer
        //if((url_showdiv1==-1) && (url_time_param==-1 )) document.getElementById("div1").style.visibility = "hidden";
        if(url_showdiv1==-1) document.getElementById("div1").style.visibility = "hidden";
        if(url_showdiv2==-1) document.getElementById("div2").style.visibility = "hidden";
        if(url_showdiv3==-1) document.getElementById("div3").style.visibility = "hidden";
        if(url_showdiv4==-1) document.getElementById("div4").style.visibility = "hidden";
        if(url_showdiv5==-1) document.getElementById("div5").style.visibility = "hidden";
        if(url_showdiv6==-1) document.getElementById("div6").style.visibility = "hidden";
        if(url_showdiv7==-1) document.getElementById("div7").style.visibility = "hidden";    
    }    

    //If we have a timer URL param then HIDE divs
    if(url_time_param!==-1 ) {
        document.getElementById("div2").style.visibility = "hidden";
        document.getElementById("div3").style.visibility = "hidden";
        document.getElementById("div4").style.visibility = "hidden";
        document.getElementById("div5").style.visibility = "hidden";
        document.getElementById("div6").style.visibility = "hidden";
        document.getElementById("div7").style.visibility = "hidden";
        //document.getElementById("div6").style.visibility = "hidden";        
    }


// (((((((((((((((((((((((((( option to disable right click v201020c ((((((((((
    var url_norightclick=location.search.substring(1).indexOf("norightclick");// != to -1 if we have this param
    if(url_norightclick!==-1) {
        document.oncontextmenu=new Function("console.log('main page: right-click-context menu -STOPPED');return false") ; //OK normal page Works  
        setInterval(function(){window.frames["sideframe1"].document.oncontextmenu = function(){console.log("setInterval sideframe1 :right click-DISABLED"); return false;}; }, 5000);
    }
// )))))))))))))))))))))))))) option to disable right click ))))))))

} // end of function init_links(){

function showItTimer() {
    //enable if its 1st click AND we have a timer UTL param
    if(first_click && url_time_param !==-1 ) {
        //alert("first_timer=true   , "+"location.search.substring(1)="+location.search.substring(1));
        //if(location.search.substring(1)>0)  alert("location.search.substring(1)="+location.search.substring(1));
        var minute=1000*60;
        console.log("first click , timers set every :");
        console.log((5*minute + additional_wait_minutes)/60/1000);
            //############ TEST ###################
            setTimeout(function(){document.getElementById("div2").style.visibility = "visible"}, 2000);//test
            setTimeout(function(){document.getElementById("div2").style.visibility = "hidden"}, 5000);//test

            setTimeout(function(){document.getElementById("div4").style.visibility = "visible"}, minute/60);//test
            setTimeout(function(){document.getElementById("div4").style.visibility = "hidden"}, 3000);//test
            // ########### TEST #####################
        setTimeout(function(){document.getElementById("div2").style.visibility = "visible"}, 5*minute+additional_wait_minutes);
        setTimeout(function(){document.getElementById("div3").style.visibility = "visible"}, 10*minute+additional_wait_minutes);
        setTimeout(function(){document.getElementById("div4").style.visibility = "visible"}, 15*minute+additional_wait_minutes);
        setTimeout(function(){document.getElementById("div5").style.visibility = "visible"}, 20*minute+additional_wait_minutes);
        setTimeout(function(){document.getElementById("div6").style.visibility = "visible"}, 25*minute+additional_wait_minutes);
        setTimeout(function(){document.getElementById("div7").style.visibility = "visible"}, 26*minute+additional_wait_minutes);

        
    }
    first_click=false;

    return false;
} // end of showItTimer()

// ((((((((((((((((addHashLabels addhashlabellinks 240110(((((((((((((((((
function addHashLabels() {
  const divprobesrv = document.getElementById('probeserver');//get probeserver contents
  //const links = document.getElementsByTagName('a');
  const links = divprobesrv.getElementsByTagName('a'); //get all href entries
  const hashRange = 99 - 1 + 1; // 01 to 99
  for (let i = 0; i < links.length; i++) {
    const url = links[i].getAttribute('href');
    const hash = getHash(url);
    // Generate prefix based on hash number
    const prefixletter=links[i].innerHTML.trim().charAt(0).toUpperCase(); //251010 Jon added trim to avoid empty spaces
    //console.log("InnetHTML="+prefixletter+ " , innerHTML="+links[i].innerHTML)
    const prefix = prefixletter+('0' + (hash % hashRange + 1)).slice(-2);
    
    // Add prefix to link description
    //links[i].innerHTML = prefix + '- ' + links[i].innerHTML; //ORIG no probeserver HTML inverted numbers
    links[i].innerHTML ='<span style="background-color: cadetblue; color: white; padding: 5px;">'+ prefix + '</span>- ' + links[i].innerHTML; //251010 Jon inverted hash numbers of probeserver html
  }
};

// Function to generate hash from URL
function getHash(url) {
  let hash = 0;
  for (let i = 0; i < url.length; i++) {
    hash = ((hash << 5) - hash) + url.charCodeAt(i);
    hash = hash & hash; // Convert to 32bit integer
  }
  return Math.abs(hash);
}
// )))))))))))))))End Of addHashLabels addhashlabellinks)))))))))))))))))))


function refreshAt(hh, mm, seconds) {
    var btime = hh.toString() + mm.toString();
    refreshAtHHMM(btime);
}

//-------------------reload at specific times 231011a --START-------------
//https://stackoverflow.com/questions/1217929/how-to-automatically-reload-a-web-page-at-a-certain-time
/*
*
* refreshAt(15,35,0); //Will refresh the page at 3:35pm
* 
* To do: add refreshAt() for all breaks and execute them if activated from pack_refresh_browser(). 
* The check can be done inside probeserver. It might need a global variable for break1 ,break2 etc "activated" of "completed"
*
*/
var refreshbreak1set=false;
var refreshbreak2set=false;
refreshbreak3set=false;
var refreshbreak4set=false;
var refreshbreak5set=false;
var refreshbreak6set=false;
var refreshbreak7olset=false;




function refreshAtHHMM(breaktime) {
      // Remove colons from breaktime
    breaktime = breaktime.replace(/:/g, '');
    var hours = breaktime.substring(0, 2);
    var minutes = breaktime.substring(2, 4);
    var seconds = "00";


    var now = new Date();
    var then = new Date();

    if(now.getHours() > hours ||
       (now.getHours() == hours && now.getMinutes() > minutes) ||
        now.getHours() == hours && now.getMinutes() == minutes && now.getSeconds() >= seconds) {
        then.setDate(now.getDate() + 1);
    }
    then.setHours(hours);
    then.setMinutes(minutes);
    then.setSeconds(seconds);
    console.log("refreshAt:"+hours+minutes+seconds)
    var timeout = (then.getTime() - now.getTime());
    setTimeout(function() { window.location.reload(true); }, timeout);
}

//-------------------reload at specific times --END-------------



//+++++++++++++++++++ probeserver v03 201025+++++++++++++++++
var server_probing_enabled=false;
var server_eval_first_run=true; //to avoid running remote probeserver eval multipletimes
var url_probeserver=location.search.substring(1).indexOf("probeserver");
if (url_probeserver!==-1)server_probing_enabled=true;

var timer_server_probe = 30000; //probe every 30 seconds
var server_probe_file="pack_refresh_browser.txt";
var jsonrequestInterval = function () {
    console.log("The request was send");
    // <hr><div id="probeserver"></div><hr> 
    var jsonrequestIntervaled = new XMLHttpRequest();
    var random_number=Math.random(); // OLD was=Date.prototype.getTime;
    jsonrequestIntervaled.open("GET", server_probe_file+"?"+random_number, true); // Date.prototype.getTime is used to avoid caching
    jsonrequestIntervaled.send();
    jsonrequestIntervaled.onreadystatechange = function () {
        if (jsonrequestIntervaled.readyState == 4) {
            console.log("The request was made and returned results (with random number="+random_number);
            var response_string =jsonrequestIntervaled.responseText;
            response_string_global=response_string;
            //console.log("DEBUG response_string="+s);
            //always add our extra text
            //document.getElementById("probeserver").innerHTML = response_string;  //ORIGINAL works replaced by v241213c
            if (jsonrequestIntervaled.status === 200  && jsonrequestIntervaled.status!=0 ) {  document.getElementById("probeserver").innerHTML = response_string; console.log("200-pack_js_footer-----ok");   } //* v241213c - Do not clear div probeserver when connection is lost.

            
            
            //in case we put the word reload, refresh browser
            if (response_string.indexOf("reload") !== -1) {
                console.log("refreshing browser");
                //document.getElementById("probeserver").innerHTML = response_string;
                window.location.reload(true);
            }

            //v201025b -evel - pack_refresh_browser.txt execute commands dynamic (requires probeserver activated) (not implemented yet)
            //in case we put the word "execute", run/eval() command
            if (response_string.indexOf("execute") !== -1) {
                var s=response_string;               
                //pack_refresh_browser = execute EVAL:console.log("eval-command-console.log--hello");:EVAL
                //var result = s.match(/AAAA:(.*?):BBBB/i); //OLD - ONLY for single line
                //var result = s.match(/AAAA:([\s\S]*?):BBBB/); //multi line                
                var result = s.match(/EVAL:([\s\S]*?):EVAL/); //multi line                
                //console.log("DEBUG +++++++++execute found3="+result[1]+"-----------------------");
                eval(result[1]);
            }

            // +++++++++++++++++++=refreshtimeXXYY++++++++++++++++++++++
            if (response_string.indexOf("refreshtime") !== -1) {
              var startIndex = response_string.indexOf("refreshtime") + 11; // Start index after "refreshtime"
              var endIndex = response_string.indexOf(":", startIndex); // Index of ":" after XX
              var refreshTime;
              
              if (endIndex !== -1) {
                // Case: refreshtimeXX:YY
                refreshTime = response_string.substring(startIndex, endIndex) + response_string.substring(endIndex + 1);
              } else {
                // Case: refreshtimeXXYY
                refreshTime = response_string.substring(startIndex);
              }
              //console.log("RefreshTime="+refreshTime);
              refreshAtHHMM(refreshTime);
            }
            //-----------------------refreshtimeXX:YY END----------------------

            if (response_string.indexOf("breakrefresh") !== -1) {
                var s=response_string;               
                //pack_refresh_browser = execute EVAL:console.log("eval-command-console.log--hello");:EVAL
                //var result = s.match(/AAAA:(.*?):BBBB/i); //OLD - ONLY for single line
                //var result = s.match(/AAAA:([\s\S]*?):BBBB/); //multi line                
                //var result = s.match(/EVAL:([\s\S]*?):EVAL/); //multi line     
                console.log("refreshbreak---");     
                if ((response_string.indexOf("breakrefresh2")!== -1) && !refreshbreak2set ) {console.log("refreshbreak2");  refreshAt("09","02",0); refreshbreak2set=true; }      
                if ((response_string.indexOf("breakrefresh3")!== -1) && !refreshbreak3set ) {console.log("refreshbreak3");  refreshAt("09","42",0); refreshbreak3set=true; }      
                if ((response_string.indexOf("breakrefresh4")!== -1) && !refreshbreak4set ) {console.log("refreshbreak4");  refreshAt(10,47,0); refreshbreak4set=true; }      
                if ((response_string.indexOf("breakrefresh5")!== -1) && !refreshbreak5set ) {console.log("refreshbreak5");  refreshAt(11,33,0); refreshbreak5set=true; }
                if ((response_string.indexOf("breakrefresh6")!== -1) && !refreshbreak6set ) {console.log("refreshbreak6");  refreshAt(12,27,0); refreshbreak6set=true; }
                //olohmero
                if ((response_string.indexOf("breakrefresh7ol")!== -1) && !refreshbreak7olset ) {
                        console.log("refreshbreak7");  refreshAt(13,20,"01"); 
                        console.log("refreshbreak8");  refreshAt(14,"00","01");
                        console.log("refreshbreak9");  refreshAt(15,"00","01");
                        //console.log("refreshbreak10TEST");  refreshAt(14,"12","01");
                        refreshbreak7olset=true; 
                    }
                if ((response_string.indexOf("breakrefreshALL")!== -1) && !refreshbreak7olset ) {
                        console.log("ALLrefreshbreak2");  refreshAt("09","02",0); refreshbreak2set=true; 
                        console.log("ALLrefreshbreak3");  refreshAt("09","42",0); refreshbreak3set=true;
                        console.log("ALLrefreshbreak4");  refreshAt(10,47,0); refreshbreak4set=true;
                        console.log("ALLrefreshbreak5");  refreshAt(11,33,0); refreshbreak5set=true;
                        console.log("ALLrefreshbreak6");  refreshAt(12,27,0); refreshbreak6set=true;
                        console.log("ALLrefreshbreak7");  refreshAt(13,20,"01"); 
                        console.log("ALLrefreshbreak8");  refreshAt(14,"00","01");
                        console.log("ALLrefreshbreak9");  refreshAt(15,"00","01");

                        //console.log("refreshbreak10TEST");  refreshAt(14,"12","01");
                        refreshbreak7olset=true; 
                    }         
                //console.log("DEBUG +++++++++execute found3="+result[1]+"-----------------------");
                //eval(result[1]);
            }

            //in case we put the word reload, refresh browser
            if (response_string.indexOf("addhashlabellinks") !== -1) {
                console.log("addhashlabellinks in div probsrv(for olohmero mostly)");
                addHashLabels();
            }

            if (response_string.includes("li_multicolor")) {
              if (response_string.includes("li_multicolor1")) {
                li_multicolor_set(1);
              } else if (response_string.includes("li_multicolor2")) {
                li_multicolor_set(2);
              } else if (response_string.includes("li_multicolor3")) {
                li_multicolor_set(3);
              } else if (response_string.includes("li_multicolor4")) {
                li_multicolor_set(4);
              } else {
                // Default to 3 if no specific number is mentioned
                li_multicolor_set(3);
              }
            } //end of if (response_string.includes("li_multicolor")) {

            if (browserVersion > 70 && swfChangeHrefToRuffle) {   modifyLinks();    }  //251118 added by jon to modify Ruffle links after each probeserver refresh    

        } //END of request Interval
    };
    
};

if(server_probing_enabled) setInterval(jsonrequestInterval, timer_server_probe);

//-------------------probeserver ---------------


// ((((((((((((((((((((((replace_url v01 190410 opengame , noopengame ((((((((((((((((((((((
//source https://stackoverflow.com/questions/4939805/change-all-links-hrefs-urls-with-vanilla-javascript-regex
function prefix_url(elem, attr) {
    var elems = document.getElementsByTagName(elem);
    for (var i = 0; i < elems.length; i++)
        //elems[i][attr] = elems[i][attr].replace('./', 'opengame.php?file=./');
        elems[i][attr] = 'opengame.php?file=' + elems[i][attr];
}

if(url_opengame!==-1) {   
    prefix_url('a', 'href');
    //replace_url('img', 'src');    
}

function prefix_remove_url(elem, attr) {
    var elems = document.getElementsByTagName(elem);
    for (var i = 0; i < elems.length; i++)
        elems[i][attr] = elems[i][attr].replace('opengame.php?file=', '');
        //elems[i][attr] = 'opengame.php?file=' + elems[i][attr];
}

if(url_opengame!==-1) {   
    prefix_remove_url('a', 'href');
    //replace_url('img', 'src');    
}

// )))))))))))))))))))))) replace_url ))))))))))))))))))))))

            /*
            // Load Javascript dynamically
            if (addhashlabellinks) {
              var secondScript = document.createElement('script');
              secondScript.type = 'text/javascript';
              secondScript.src = 'test_hash.js';
              console.log("addhashlabellinks INSIDE");
              secondScript.onload = function() {
                // code to execute after the second script is loaded and executed
                addHashLabels();
              };
              document.head.appendChild(secondScript);
            }                        
            */
// ((((((((((((((((((((((replace_swfChangeHrefToRuffle v01 211128 opengame Ruffle, noopengame ((((((((((((((((((((((
//<!-- Modify HREF for ruffle 241128a (add it to the end of the script)-->

    // Function to get the Chrome version
    function getBrowserVersion() {
        const userAgent = navigator.userAgent;
        //const match = userAgent.match(/Chrom(e|ium)\/([0-9]+)\./);
        //return match ? parseInt(match[2], 10) : null;
	    // Detect Chrome or Chromium (including version number)
	    const chromeMatch = userAgent.match(/Chrom(e|ium)\/([0-9]+)\./);
	    if (chromeMatch) {
	        return parseInt(chromeMatch[2], 10);
	    }

	    // Detect Firefox (including version number)
	    const firefoxMatch = userAgent.match(/Firefox\/([0-9]+)\./);
	    if (firefoxMatch) {
	        return parseInt(firefoxMatch[1], 10);
	    }
	    // If neither Chrome nor Firefox is found
	    return 0;        
    }
 
    // Function to modify href links
    function modifyLinks() {
        const links = document.querySelectorAll('a');
        const prefix = './opengame_ruffle.php?file=';
 
        links.forEach(link => {
            const currentHref = link.getAttribute('href');
            //if (currentHref && currentHref.includes('.swf')  && !currentHref.includes('ruffle') ) { //jon 250128 changed by jon - it ignored games that contained ruffle in their name
            if (currentHref && currentHref.includes('.swf')  && !currentHref.includes('opengame_ruffle') ) {    
                //link.setAttribute('href', prefix + encodeURIComponent(currentHref)); //ORIG works but shows %2F instead of '/'
                link.setAttribute('href', prefix + (currentHref));
            }
        });
    }
 
    // Check Chrome version and modify links if necessary
    const browserVersion = getBrowserVersion();
    console.log("-------------CHROMEversion="+browserVersion);
    if (browserVersion > 70 && swfChangeHrefToRuffle) {
        modifyLinks();
    }

// )))))))))))))))))))))) replace_swfChangeHrefToRuffle ))))))))))))))))))))))




// ((((((++++jon 251111  add random colors to <li> elements((((((((((((((((((((((((((((((((((((((((((

function li_multicolor_set(id) {
  // Function to apply style to an element
    function styleElement(el, color) {
      el.style.backgroundColor = color;
      if (el.tagName === 'A') {
        //el.style.color = color; // Make sure link text is visible
        el.style.color = 'initial';
        //el.style.textDecoration = 'none'; // Optionally remove underline for visibility
      }
    }

  // Function to generate color based on index and id
  function getColorForIndex(index) {
    if (id === 1) {
      const greyShade = Math.floor(Math.random() * 0xEEEEEE);
      return '#' + greyShade.toString(16).padStart(6, '0');
    } else if (id === 2) {
      const r = Math.floor(Math.random() * 56) + 200;
      const g = Math.floor(Math.random() * 56) + 200;
      const b = Math.floor(Math.random() * 56) + 200;
      return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
    } else if (id === 3) {
      const step = 20;
      const maxColor = 255;
      const minColor = 200;
      let r = minColor + (index * step) % (maxColor - minColor + 1);
      let g = minColor + (index * step) % (maxColor - minColor + 1);
      let b = minColor + (index * step) % (maxColor - minColor + 1);
      r = r > maxColor ? minColor : r;
      g = g > maxColor ? minColor : g;
      b = b > maxColor ? minColor : b;
      return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
    } else if (id === 4) {
      const colorNames = [
        'LightPink', 'LightBlue', 'LightGreen', 'LightYellow', 'LightCoral',
        'LightCyan', 'Lavender', 'PeachPuff', 'MintCream', 'Apricot',
        'Salmon', 'Khaki', 'Plum', 'LightSalmon'
      ];
      return colorNames[index % colorNames.length];
    }
  }

  // Style all existing <li> elements
  const listItems = document.querySelectorAll('li');
  listItems.forEach((li, index) => {
    const color = getColorForIndex(index);
    styleElement(li, color);
  });

  // Style all existing <a> elements NOT inside <li>
  const allLinks = document.querySelectorAll('a');
  const linksOutsideLi = Array.prototype.filter.call(allLinks, a => !a.closest('li'));
  linksOutsideLi.forEach((a, index) => {
    const color = getColorForIndex(index);
    styleElement(a, color);
  });

  // Setup MutationObserver for future <li> and <a> inside #probeserver
  const container = document.getElementById('probeserver');
  if (container) {
    const observer = new MutationObserver(mutations => {
      mutations.forEach(mutation => {
        mutation.addedNodes.forEach(node => {
          if (node.nodeType !== 1) return; // Only process element nodes
          
          if (node.tagName === 'LI') {
            const index = Array.prototype.indexOf.call(container.querySelectorAll('li'), node);
            const color = getColorForIndex(index);
            styleElement(node, color);
          } else if (node.tagName === 'A') {
            const index = Array.prototype.indexOf.call(container.querySelectorAll('a'), node);
            const color = getColorForIndex(index);
            styleElement(node, color);
          } else {
            // Also check for <a> elements inside added nodes
            const aTags = node.querySelectorAll ? node.querySelectorAll('a') : [];
            aTags.forEach((a, index) => {
              const globalIndex = Array.prototype.indexOf.call(container.querySelectorAll('a'), a);
              const color = getColorForIndex(globalIndex);
              styleElement(a, color);
            });
          }
        });
      });
    });

    observer.observe(container, { childList: true, subtree: true });
  }
}
//)))))END of jon  add random colors to <li> elements))))))))))))))))))))))))))))))))))))))))))



//</script>
