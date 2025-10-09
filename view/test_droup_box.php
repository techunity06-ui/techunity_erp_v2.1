<form name="form1" id="form1" onsubmit="return false;">
        <table width="100%">
            <tr>
                <td>
                    <select name="leftProcess" size="5">
                        <option value="1">Left List Option 1</option>
                        <option value="2" onclick="open_model(2);">Left List Option 2</option>
                    </select>
                </td>
                <td>
                    <button onclick="moveRight('leftProcess','rightProcess');">>></button>
                    <br/>
                    <button onclick="moveRight('rightProcess','leftProcess')">
                        <<</button>
                </td>
                <td>
                    <select name="rightProcess" size="5">
                        <option value="3">Right List Option 1</option>
                        <option value="4">Right List Option 2</option>
                    </select>
                </td>
            </tr>
        </table>
    </form>

   <script type="text/javascript">
   	window.onload=function() {
    if (Storage) {
        var leftSelect = document.forms["form1"].elements["leftProcess"];
        var rightSelect = document.forms["form1"].elements["rightProcess"];
        if (localStorage.leftOptions.length>0) {
            clearSelect(leftSelect);
            fillSelect(leftSelect, JSON.parse(localStorage.leftOptions));
        }
        if (localStorage.rightOptions.length>0) {
            clearSelect(rightSelect);
            fillSelect(rightSelect,JSON.parse( localStorage.rightOptions));
        } else {
            alert("local storage not supported");
        }
    }else{
    alert("not supported")
    }
}

function moveRight(leftValue, rightValue) {
    //alert("Elft value is t : "+leftValue);
    var leftSelect = document.forms["form1"].elements[leftValue];
    var rightSelect = document.forms["form1"].elements[rightValue];

    //alert("test : " + document.forms["form1"].elements[myLeftId].options[selItem].value);
    if (leftSelect.selectedIndex == -1) {
        window.alert("You must first select an item on the left side.")
    } else {
        var option = leftSelect.options[leftSelect.selectedIndex];
        rightSelect.appendChild(option);
    }
}
window.onbeforeunload = function (event) //before refresh
{
    saveCurrentState(document.forms["form1"].elements["leftProcess"], document.forms["form1"].elements["rightProcess"]);

}

function saveCurrentState(leftSelect, rightSelect) {
    if (Storage) {
       var leftOptions = [];
        for (var i=0;i<leftSelect.options.length;i++) {
            leftOptions.push({
                "value": leftSelect.options[i].value,
                    "innerHTML": leftSelect.options[i].innerHTML
            });
        }
    }
    var rightOptions = [];
    for (var i=0;i<rightSelect.options.length;i++) {
        rightOptions.push({
            "value": rightSelect.options[i].value,
                "innerHTML": rightSelect.options[i].innerHTML
        });
    }
    localStorage.leftOptions=JSON.stringify(leftOptions);
    localStorage.rightOptions=JSON.stringify(rightOptions);
}


function fillSelect(select, options) {
    for (i = 0; i < options.length; i++) {
        var opt = document.createElement('option');
        opt.value = options[i].value;
        opt.innerHTML = options[i].innerHTML;
        select.appendChild(opt);
    }
}

function clearSelect(select) {
   select.innerHTML="";
}

function open_model(val){
	alert(val);
}

$('select option').live('click', function() {
   alert('hello')
})
   </script>