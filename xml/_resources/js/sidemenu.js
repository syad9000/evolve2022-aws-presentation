/* Custom JavaScript *//* sidemenu.js */
Number.prototype.isInteger=function(e){return(0^e)===e};
function fixPath(url){
	if(!url) return 0;

	// Remove file extension
	if(url.indexOf(".") >= 0)
		url = url.substring(url.indexOf(".") , -1);

	// Add slash if it doesn't have one;
	return url.substring(url.length - 1, url.length) === "/" ? url : url + "/"; 
}
function findFamily(el){
	if(!el) return 0;

	// Add class "active" to the parent LI
	if(el.parentElement.nodeName == "UL" && el.parentElement.parentElement.nodeName == "LI"){
		cclass = (el.parentElement.parentElement.getAttribute("class")) ? el.parentElement.parentElement.getAttribute("class") + " " : "";
		el.parentElement.parentElement.setAttribute("class",  cclass + "active");
	}

	// Set the first OL child within this LI to have class "active"
	if (el.hasChildNodes()) {
	  var children = el.childNodes;

	  for (var i = 0; i < children.length; i++) {
		if(children[i].nodeName == "UL"){
			cclass = (children[i].getAttribute("class")) ? children[i].getAttribute("class") + " " : "";
			children[i].setAttribute("class",  cclass + "active");
			break;
		}
	  }
	}
	// Recurse up the tree till you hit the NAV element
	if(el.parentElement.nodeName !== "NAV")
		findFamily(el.parentElement);
}
function addMenuClasses(selector){
	var li = document.querySelectorAll(selector),
		link,
		path;
	
	if( li.length ){
		path = fixPath(location.pathname);

		for(var i in li){
			if (!li.hasOwnProperty(i)) 
				continue;

			if(li[i].getElementsByTagName("a")[0] != undefined)
				link = fixPath(li[i].getElementsByTagName("a")[0].pathname);
			

			// Found a selected link
			if(link &&  link == path){
				li[i].classList.add("active");
				li[i].getElementsByTagName("a")[0].classList.add("sel");
				findFamily(li[i]);
			} else if(path.indexOf(link) > -1){
				li[i].classList.add("active");
			}
		}
	}
}
