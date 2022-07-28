$cityToEdit = document.querySelectorAll('#city_edit');
let $cpt = 0;
function modifCity(event)
{


   const id = event.currentTarget.getAttribute("title");
   const ville = event.currentTarget.parentNode.parentNode.firstChild.firstChild.valueOf().value
   const cp = event.currentTarget.parentNode.parentNode.childNodes[1].firstChild.valueOf().value
   event.currentTarget.href = "/hangoutcom/public/city/edit/" + id +"/"+  ville + "/" + cp

}
function edit(e){
   $cpt++;
   if ($cpt === 1)
   {
      e.currentTarget.firstChild.removeAttribute("class")
      e.currentTarget.firstChild.setAttribute("class","fa-solid fa-floppy-disk")
      e.currentTarget.parentNode.parentNode.childNodes[0].replaceWith(document.createElement("input"))
      e.currentTarget.parentNode.parentNode.childNodes[0].setAttribute("value",e.currentTarget.parentNode.parentNode.childNodes[1].valueOf().innerHTML)
      e.currentTarget.parentNode.parentNode.childNodes[1].remove()
      e.currentTarget.parentNode.parentNode.childNodes[0].setAttribute("class","inptFormCityAdd")
      e.currentTarget.parentNode.parentNode.childNodes[0].setAttribute("style","margin-left:10px")
      e.currentTarget.parentNode.parentNode.prepend(document.createElement("td"))
      e.currentTarget.parentNode.parentNode.firstChild.appendChild(e.currentTarget.parentNode.parentNode.childNodes[1])
      e.currentTarget.parentNode.parentNode.childNodes[1].replaceWith(document.createElement("input"))
      e.currentTarget.parentNode.parentNode.childNodes[1].setAttribute("value",e.currentTarget.parentNode.parentNode.childNodes[2].valueOf().innerHTML)
      e.currentTarget.parentNode.parentNode.childNodes[2].remove()
      e.currentTarget.parentNode.parentNode.childNodes[1].setAttribute("class","inptFormZipAdd")
      e.currentTarget.parentNode.parentNode.childNodes[1].setAttribute("style","margin-left:10px")
      e.currentTarget.parentNode.parentNode.childNodes[1].setAttribute("type","number")
      e.currentTarget.parentNode.parentNode.insertBefore(document.createElement("td"),e.currentTarget.parentNode.parentNode.childNodes[2])
      e.currentTarget.parentNode.parentNode.childNodes[2].appendChild(e.currentTarget.parentNode.parentNode.childNodes[1])
      e.currentTarget.addEventListener('click',modifCity)
   }


}
$cityToEdit.forEach((item,index)=>{item.addEventListener('click',edit);})

