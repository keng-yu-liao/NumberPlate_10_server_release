var YOUR_NUM_NUM = "yournum-num";
var CURRENT_NUM_NUM = "currentnum-num";

window.onload = function what() {
    var url = this.location.href;
    var yourWaitNum = this.getYourWaitNum(url);
    var storeTableName = this.getStoreTableName(url);

    
    rotateMonitor();
    setYourWaitNum(yourWaitNum);
    insertWaitNum(storeTableName, yourWaitNum);
    getLastDoneNum(storeTableName, yourWaitNum);
    updateLastNum(storeTableName, yourWaitNum);
}

function rotateMonitor() {
    window.addEventListener("orientationchange", function(){
	if(screen.orientation.angle == 90 || screen.orientation.angle == 270) {
	    alert("請轉為直立式顯示");
	}        
    });
}

function createXMLHttpRequest() {
    if(window.XMLHttpRequest) {
        return new XMLHttpRequest();

    } else {
        return new ActiveXObject("Microsoft.XMLHTTP");

    }

}

function getStoreTableName(oriUrl) {
    return oriUrl.split("?")[1].split("&")[0].split("=")[1];

}

function getYourWaitNum(oriUrl) {
    return oriUrl.split("?")[1].split("&")[1].split("=")[1];    

}

function getLastDoneNum(storeTableName, yourWaitNum) {
    var xmlHttp = createXMLHttpRequest();
    var url = NUMBER_PLATE10_DOMAIN + GET_LAST_DONE_NUM_API + "?storeTableName=" + storeTableName;

    xmlHttp.onreadystatechange = function() {
        getLastDoneNumCallback(xmlHttp, storeTableName, yourWaitNum);
    }
    xmlHttp.open(HTTP_TYPE, url);
    xmlHttp.send();

}

function compareDoneNum(storeTableName, yournum) {
    var numIndex = getNumIndex(yournum);

    var xmlHttp = createXMLHttpRequest();
    var url = NUMBER_PLATE10_DOMAIN + COMPARE_DONE_NUM_API + "?storeTableName=" + storeTableName + "&yourNum=" + yournum + "&numIndex=" + numIndex;

    xmlHttp.onreadystatechange = function() {
        compareDoneNumCallback(xmlHttp, storeTableName, yournum);

    }
    xmlHttp.open(HTTP_TYPE, url);
    xmlHttp.send();

}

function insertWaitNum(storeTableName, insertWaitNum) {
    var xmlHttp = createXMLHttpRequest();

    var url = NUMBER_PLATE10_DOMAIN + INSERT_WAIT_NUM_API + "?storeTableName=" + storeTableName + "&insertWaitNum=" + insertWaitNum;

    xmlHttp.onreadystatechange = function() {
        insertWaitNumCallback(xmlHttp);

    }
    xmlHttp.open(HTTP_TYPE, url);
    xmlHttp.send();

}

function updateLastNum(storeTableName, updateLastNum) {
    var xmlHttp = createXMLHttpRequest();

    var url = NUMBER_PLATE10_DOMAIN + UPDATE_LAST_NUM_API + "?tableName=" + storeTableName + "&updateLastNum=" + updateLastNum;
    xmlHttp.onreadystatechange = function() {
        updateLastNumCallback(xmlHttp);

    }

    xmlHttp.open(HTTP_TYPE, url);
    xmlHttp.send();

}

function getLastDoneNumCallback(xmlHttp, storeTableName, yourWaitNum) {
    if(xmlHttp.readyState==4) {
        if(xmlHttp.status==200) {
            var lastDoneNum = parseJson(xmlHttp.responseText).data;
            document.getElementById(CURRENT_NUM_NUM).innerHTML = lastDoneNum;
            compareDoneNum(storeTableName, yourWaitNum);
        
        } else {
            alert("系統錯誤: getLastDoneNum");

        }  
    }

}

function compareDoneNumCallback(xmlHttp, storeTableName, yourNum) {
    if(xmlHttp.readyState==4) {
        if(xmlHttp.status==200) {
            var compareResult = parseJson(xmlHttp.responseText).data;
            
            if(compareResult == COMPARE_RESULT_YES) {
                setCalledStatus();
                //setTimeout(function() {getLastDoneNum(storeTableName, yourNum);}, 4000);

            } else {
                setTimeout(function() {getLastDoneNum(storeTableName, yourNum);}, 2000);

            }

        } else {
            alert("系統錯誤: compareDoneNum");

        }

    }

}

function updateLastNumCallback(xmlHttp) {
    if(xmlHttp.readyState==4) {
        if(xmlHttp.status==200) {
            var result = parseJson(xmlHttp.responseText).status;
            if(result == STATUS_FAIL) {
                alert("系統錯誤: updateLastNum");

            }

        }

    }

}

function insertWaitNumCallback(xmlHttp) {
    if(xmlHttp.readyState==4) {
        if(xmlHttp.status==200) {
            var result = parseJson(xmlHttp.responseText).status;
            if (result == STATUS_FAIL) {
                alert("系統錯誤: insertWaitNum");

            }
        }
    }

}

function getNumIndex(updateWaitNum) {
    var updateWaitNum = parseInt(updateWaitNum, 10) + 10;
    var updateWaitNumStr = updateWaitNum.toString();
    return updateWaitNumStr.split("")[0];

}

function compareWaitNum(lastWaitNum) {
    var yourNumInt = parseInt(document.getElementById(YOUR_NUM_NUM).innerHTML, 10);
    var lastWaitNumInt = parseInt(lastWaitNum);
    
    if (lastWaitNumInt >= yourNumInt) {
        return true;

    } else {
        return false;

    }

}

function setYourWaitNum(yourWaitNum) {
    document.getElementById(YOUR_NUM_NUM).innerHTML = yourWaitNum;

}

function setCalledStatus() {
    document.getElementById("yournum-bg").style.backgroundColor = "#ff2d2d";
    document.getElementById("takemeal-tv").style.visibility = "visible";

}

function parseJson(responseText) {
    var jsonObj = JSON.parse(responseText);
    return jsonObj;

}
