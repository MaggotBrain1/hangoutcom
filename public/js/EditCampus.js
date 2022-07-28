$campusToEdit = document.querySelectorAll('#edit_campus');
let $cptCampus = 0;
function modif(event)
{
        const id = event.currentTarget.getAttribute("title");
        const campus = event.currentTarget.parentNode.parentNode.firstChild.firstChild.valueOf().value
        event.currentTarget.href = "/hangoutcom/public/campus/edit/" + id +"/"+  campus
}
function edit(e) {
    $cptCampus++;
    if ($cptCampus === 1) {
        e.currentTarget.firstChild.removeAttribute("class")
        e.currentTarget.firstChild.setAttribute("class", "fa-solid fa-floppy-disk")
        e.currentTarget.parentNode.parentNode.childNodes[0].replaceWith(document.createElement("input"))
        e.currentTarget.parentNode.parentNode.childNodes[0].setAttribute("value",e.currentTarget.parentNode.parentNode.childNodes[1].valueOf().innerHTML)
        e.currentTarget.parentNode.parentNode.childNodes[1].remove()
        e.currentTarget.parentNode.parentNode.childNodes[0].setAttribute("class","inptFormCityAdd")
        e.currentTarget.parentNode.parentNode.childNodes[0].setAttribute("style","margin-left:10px")
        e.currentTarget.parentNode.parentNode.prepend(document.createElement("td"))
        e.currentTarget.parentNode.parentNode.firstChild.appendChild(e.currentTarget.parentNode.parentNode.childNodes[1])
        e.currentTarget.addEventListener('click',modif)
    }
}

    $campusToEdit.forEach((item,index)=>{item.addEventListener('click',edit);})