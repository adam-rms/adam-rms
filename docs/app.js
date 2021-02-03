// App logic.
"js-hide-no-instance" "js-hide-no-projects"
js-auth-logout
js-auth-not-permission and js-auth-permission
js-nav data-nav=""

js-instance-fill-name
js-auth-data-fill-name

{% for instance in USERDATA.instances %}
<a href="{{ CONFIG.ROOTURL }}?i={{instance.instances_id}}" class="dropdown-item">
    {{instance.instances_name}}
    <p class="text-sm text-muted">{{ instance.userInstances_label }}</p>
    {% if USERDATA.instance.instances_id == instance.instances_id %}
    <div class="d-block d-sm-none">
        <span class="float-right text-muted text-sm">Selected</span>
    </div>
    {% endif %}
</a>
<div class="dropdown-divider"></div>
{% endfor %}

window.myApp = {};
myApp.config = {
    //endpoint: 'http://localhost/api/',
    endpoint: 'https://dash.adam-rms.com/api/',
    version: {
        code: 'WEB',
        number: 'WEB'
    }
}
myApp.data = {
    instances: [],
    instance: [],
    instanceID: null,
    projects: {},
    init: function() {
        myApp.data.instances = [];
        myApp.data.instanceID = localStorage.getItem('instanceID');
        myApp.data.instance= [];
        myApp.data.projects= {};
    }
}
myApp.auth = {
    token: false,
    location: {
        type:false,
        value:"Not set"
    },
    logout: function() {
        localStorage.setItem('token','');
        $("#app-mainview").hide();
        $("#login").show();
        if (navigator.app) {
            navigator.app.exitApp();
        }
    },
    login: function() {
        var username = $("#login-username").val();
        var password = $("#login-password").val();
        if (username != '' && password != '') {
            $.ajax({
                type: "POST",
                url: myApp.config.endpoint + 'auth/appLogin.php',
                dataType: 'json',
                data: {
                    'email': username,
                    'password': password,
                    'deviceData':
                        'Device Model:' + ',' +
                        'Device Cordova:' +',' +
                        'Device Platform:' + ',' +
                        'Device UUID:' + ',' +
                        'Device Version:' + ',' +
                        'Connection Type:'
                },
                success: function (response) {
                    if (response.result) {
                        localStorage.setItem('token', response.response.token);
                        myApp.auth.token = response.response.token;
                        myApp.functions.launchApp();
                    } else {
                        alert(response.error.message);
                    }
                },
                error: function (request, status, error) {
                    console.log(JSON.stringify(request));
                    console.log(JSON.stringify(error));
                    console.log(JSON.stringify(status));
                    if (request.statusText == "error" || requset.statusText == "") {
                        ons.notification.alert("Error connecting to AdamRMS - Please check your connection");
                    } else {
                        ons.notification.alert(request.statusText);
                    }

                }
            });
        } else {
            alert("Please enter a username and password");
        }
    },
    changeInstance: function() {
        var listOfInstances = [];
        $(myApp.data.instances).each(function (index, element) {
            listOfInstances.push(element['instances_name']);
        });
        listOfInstances.push({
            label: 'Cancel',
            icon: 'md-close'
        });
        ons.openActionSheet({
            title: 'Change Business',
            cancelable: true,
            buttons: listOfInstances
        }).then(function (index) {
            if (index >= 0 && index < myApp.data.instances.length) { //-1 is used to show a cancel and a number greater than the length of the array means it's also cancel
                myApp.data.instanceID = myApp.data.instances[index]['instances_id']
                localStorage.setItem('instanceID',myApp.data.instanceID)

                //reset location
                myApp.auth.location = {
                    type:false,
                    value:"Not set"
                };
                $("#tabbarPageTitle").html("AdamRMS");

                myApp.controllers.firstBoot();
            }
        });
    },
    setLocation: function() {
        var options = [{
            label: 'Scan',
            icon: 'fa-camera'
        },
            {
                label: 'Enter Manually',
                icon: 'fa-pencil'
            },
            {
                label: 'Cancel',
                icon: 'md-close'
            }
        ];
        ons.openActionSheet({
            title: 'Set Location',
            cancelable: true,
            buttons: options
        }).then(function (index) {
            if (index >= 0 && index < 2) { //-1 is used to show a cancel and a number greater than the length of the array means it's also cancel
                if (index === 0) {
                    myApp.functions.barcode.scan(false, function(text,type) {
                        if (text !== false) {
                            if (type === "Fake") {
                                type = "CODE_128";
                            }
                            myApp.functions.apiCall("assets/barcodes/search.php", {"text":text,"type":type}, function (result) {
                                if (result.location) {
                                    myApp.auth.location.value = result.location['barcode']["locationsBarcodes_id"];
                                    myApp.auth.location.name = result.location['locations_name'];
                                    myApp.auth.location.type = "barcode";
                                } else if (result.asset) {
                                    console.log(result.asset);
                                    myApp.auth.location.value = result.asset['assets_id'];
                                    myApp.auth.location.name = result.asset['tag'] + " " + result.asset['assetTypes_name'];
                                    myApp.auth.location.type = "asset";
                                } else {
                                    ons.notification.toast("Sorry location not found", { timeout: 2000 });
                                }
                                $("#tabbarPageTitle").html("AdamRMS - " + myApp.auth.location.name);
                            });
                        }
                    });
                } else if (index === 1) {
                    ons.notification.prompt({ message: 'Description for your location',title: 'Set Location' }).then(function(result) {
                        myApp.auth.location.value = myApp.functions.escapeHtml(result);
                        myApp.auth.location.name = myApp.functions.escapeHtml(result);
                        myApp.auth.location.type = "Custom";
                        $("#tabbarPageTitle").html("AdamRMS - " + myApp.auth.location.name);
                    });
                }
            }
        });
    },
    instanceHasPermission(permissionid) {
        if (myApp.auth.token === false || !myApp.data.instance) {
            return false;
        }
        if (myApp.data.instance['permissionsArray'].includes(permissionid)) {
            return true;
        } else {
            return false;
        }
    }
}
myApp.functions = {
    escapeHtml: function(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    },
    s3url: function(fileid,callback) {
        myApp.functions.apiCall("file/",{"f":fileid,"d":"force"}, function(response) {
            callback(response.url);
        });
    },
    apiCall: function (endpoint, data, callback, useCustomLoader) {
        if (typeof data !== 'object' || data === null) {
            data = {}
        }
        data['jwt'] = myApp.auth.token;
        if (myApp.data.instanceID !== null) {
            data['instances_id'] = myApp.data.instanceID;
        }
        var connected = true;
        try {
            //For some unknown reason Android gets upset about this
            if (navigator.connection.type === Connection.NONE) {
                connected = false;
            }
        } catch(err) {
            console.log(err.message);
        }
        if (connected !== true) {
            ons.notification.toast("No Network Connection", { timeout: 2000 });
        } else {
            if (useCustomLoader !== true) {
                $(".loadingDialog").show();
                //document.querySelector('#mySplitter').left.close();
            }
            $.ajax({
                type: "POST",
                url: myApp.config.endpoint + endpoint,
                dataType: 'json',
                data: data,
                success: function (response) {
                    if (useCustomLoader !== true) {
                        $('.loadingDialog').hide();
                    }
                    console.log("Got ajax data - going to call callback");
                    if (response.result) {
                        console.log("Calling callback");
                        callback(response.response);
                    } else {
                        if (response.error.code && response.error.code == "AUTH") {
                            //They need to login again - token probably expired
                            myApp.auth.logout();
                        } else {
                            ons.notification.toast(response.error.message, {timeout: 3000});
                        }
                    }
                    console.log(JSON.stringify(response));
                },
                error: function (request, status, error) {
                    $('.loadingDialog').hide();
                    console.log(JSON.stringify(error));
                    ons.notification.alert({title: request.statusText + " - " + status, message: (request.responseText ? request.responseText : 'Error connecting to AdamRMS') }, function () {
                        myApp.controllers.firstBoot();
                        if (navigator.app) {
                            myApp.controllers.firstBoot();
                        }
                    });
                }
            });
        }
    },
    launchApp: function () {
        myApp.controllers.firstBoot();
        $("#login").hide();
        $("#app-mainview").show();
    },
    fileExtensionToIcon: function(extension) {
        switch (extension.toLowerCase()) {
            case "gif":
                return 'fa-file-image';
                break;

            case "jpeg":
                return 'fa-file-image';
                break;

            case "jpg":
                return 'fa-file-image';
                break;

            case "png":
                return 'fa-file-image';
                break;

            case "pdf":
                return 'fa-file-pdf';
                break;

            case "doc":
                return 'fa-file-word';
                break;

            case "docx":
                return 'fa-file-word';
                break;

            case "ppt":
                return 'fa-file-powerpoint';
                break;

            case "pptx":
                return 'fa-file-powerpoint';
                break;

            case "xls":
                return 'fa-file-excel';
                break;

            case "xlsx":
                return 'fa-file-excel';
                break;

            case "csv":
                return 'fa-file-csv';
                break;

            case "aac":
                return 'fa-file-audio';
                break;

            case "mp3":
                return 'fa-file-audio';
                break;

            case "ogg":
                return 'fa-file-audio';
                break;

            case "avi":
                return 'fa-file-video';
                break;

            case "flv":
                return 'fa-file-video';
                break;

            case "mkv":
                return 'fa-file-video';
                break;

            case "mp4":
                return 'fa-file-video';
                break;

            case "gz":
                return 'fa-file-archive';
                break;

            case "zip":
                return 'fa-file-archive';
                break;

            case "css":
                return 'fa-file-code';
                break;

            case "html":
                return 'fa-file-code';
                break;

            case "js":
                return 'fa-file-code';
                break;

            case "txt":
                return 'fa-file-alt';
                break;
            default:
                return 'fa-file';
                break;
        }
    },
    formatSize: function(size) {
        if (size >= 1073741824) {
            size = (size / 1073741824).toFixed(1) + ' GB';
        } else if (size >= 100000) {
            size = (size / 1048576).toFixed(1) + ' MB';
        } else if (size >= 1024) {
            size = (size / 1024).toFixed(0) + ' KB';
        } else if (size > 1) {
            size = size + ' bytes';
        } else if (size == 1) {
            size = size + ' byte';
        } else {
            size = '0 bytes';
        }
        return size;
    },
    nl2br: function(text) {
        if (text == null) {
            return text;
        } else {
            return text.replace(/(?:\r\n|\r|\n)/g, '<br>');
        }
    },
    reset: function() {
        localStorage.clear();
        location.reload();
    }
}
myApp.controllers = {
    firstBoot: function() {
        //Called when app opened or when the instance is changed
        console.log("Running first boot");
        myApp.data.init();
        myApp.controllers.menu.loadNavigation();
    },
    menu: {
        loadNavigation: function () {
            console.log("Loading navigation");
            myApp.functions.apiCall("instances/list.php", {}, function (result) {
                if (result.length < 1) {
                    //User has no instances so can't use the app
                    ons.notification.alert({message: 'You\'re not a member of any Businesses - visit the website to set one up',title:'Business not found'})
                        .then(function() {
                            myApp.auth.logout();
                        });
                } else {
                    myApp.controllers.assets.fullAssetList(function () {
                        console.log("First asset list logged");
                    },null,true);

                    myApp.data.instances = [];
                    $(result).each(function (index, element) {
                        //Parse instance permissions
                        element.permissionsArray = [];
                        if (Array.isArray(element['permissions'])) {
                            //Block if not an array
                            var i;
                            var arraylength = element['permissions'].length;
                            for (i = 0; i < arraylength; i++) {
                                if (element['permissions'][i] != null && parseInt(element['permissions'][i]) !== NaN) {
                                    element.permissionsArray.push(parseInt(element['permissions'][i]));
                                }
                            }
                        }
                        myApp.data.instances.push(element);
                        if (element['this']) {
                            //This instance
                            myApp.data.instance = element;
                            myApp.data.instanceID = element['instances_id'];
                            $("#menu-title").html(element['instances_name']);
                            if (myApp.auth.instanceHasPermission(20)) {
                                myApp.functions.apiCall("projects/list.php", {}, function (projectResult) {
                                    $("#menu-projects-list").html("");
                                    $(projectResult).each(function (index, element) {
                                        myApp.data.projects[element['projects_id']] = element;
                                        $("#menu-projects-list").append('<ons-list-item tappable modifier="longdivider"  onclick="document.querySelector(\'#myNavigator\').pushPage(\'project.html\', {data: {id: ' + element['projects_id'] + '}});">' +
                                            '<div class="left">' +
                                            (element.thisProjectManager ? '<ons-icon icon="fa-dot-circle" style="color: #ffc107;"></ons-icon>' : '<ons-icon icon="fa-circle" style="color: grey;"></ons-icon>')+
                                            '</div>' +
                                            '<div class="center"><span class="list-item__title">' + element['projects_name'] + '</span><span class="list-item__subtitle">' + element['clients_name'] + '</span></div>' +
                                            '</ons-list-item>');
                                    });
                                });
                            }
                            if (myApp.auth.instanceHasPermission(17)) {
                                $("#menu-asset-addNewButton").show();
                            }
                            if (myApp.auth.instanceHasPermission(85)) {
                                $(".scanSpeedDial").show();
                            } else {
                                $(".scanSpeedDial").hide();
                            }
                        }
                    });
                }
            })
        }
    },
    assets: {
        barcodeDeleteFAB: function() {
            if (myApp.auth.location.type !== false) {
                if (myApp.auth.instanceHasPermission(86)) {
                    myApp.functions.barcode.scan(false, function(text,type) {
                        if (text !== false) {
                            if (type === "Fake") {
                                type = "CODE_128";
                            }
                            myApp.functions.apiCall("assets/barcodes/search.php", {"text":text,"type":type,"location":myApp.auth.location.value,"locationType":myApp.auth.location.type}, function (assetResult) {
                                if (assetResult.asset === false) {
                                    ons.notification.toast("Sorry barcode not found", {timeout: 2000});
                                } else {
                                    ons.notification.confirm({
                                        title: "Delete Barcode",
                                        message: "Are you sure you'd like to delete the association for this barcode?"
                                    }).then(function (result) {
                                        if (result === 1) {
                                            myApp.functions.apiCall("assets/barcodes/delete.php", {"barcodes_id": assetResult.barcode}, function (result) {
                                                ons.notification.toast("Barcode deleted", {timeout: 2000});
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    });
                } else {
                    ons.notification.toast("Sorry you can't delete barcodes in this business", { timeout: 2000 });
                }
            } else {
                ons.notification.toast("Please set a location before attempting to scan a barcode", { timeout: 2000 });
            }
        },
        barcodeScanFAB: function() {
            if (myApp.auth.location.type !== false) {
                myApp.functions.barcode.scan(false, function(text,type) {
                    if (text !== false) {
                        if (type === "Fake") {
                            type = "CODE_128";
                        }
                        myApp.controllers.assets.barcodeScanPostScan(text,type);
                    }
                });
            } else {
                ons.notification.toast("Please set a location before attempting to scan a barcode", { timeout: 2000 });
            }
        },
        barcodeScanPostScan: function(text,type) {
            myApp.functions.apiCall("assets/barcodes/search.php", {"text":text,"type":type,"location":myApp.auth.location.value,"locationType":myApp.auth.location.type}, function (assetResult) {
                if (assetResult.asset === false) {
                    if (assetResult.barcode !== false) {
                        //Barcode exists but asset doesn't
                        var barcodeid = assetResult.barcode;
                    } else {
                        //This is a totally random new barcode
                        var barcodeid = false;
                    }

                    if (myApp.auth.instanceHasPermission(88)) {
                        ons.notification.confirm({
                            title: "Unassociated Barcode",
                            message: "Would you like to associate it with an asset in " + myApp.data.instance['instances_name'] + "?"
                        }).then(function (result) {
                            if (result === 1) {
                                ons.notification.prompt({
                                    title: "Associate Barcode",
                                    message: 'What is the Asset\'s Tag?'
                                }).then(function (tag) {
                                    if (tag) {
                                        myApp.functions.apiCall("assets/barcodes/assign.php", {
                                            "tag": tag,
                                            "barcodeid": barcodeid,
                                            "text": text,
                                            "type": type
                                        }, function (result) {
                                            myApp.controllers.assets.barcodeScanPostScan(text,type);
                                        });
                                    }
                                });
                            }
                        });
                    } else {
                        ons.notification.toast("Sorry barcode not found", { timeout: 2000 });
                    }
                } else {
                    if (typeof myApp.data.assetTypes[assetResult.asset.assetTypes_id] === "undefined") {
                        //We don't currently have this one downloaded so lets go grab it
                        var requestData = {"assetTypes_id": assetResult.asset.assetTypes_id,"all":true };
                        myApp.functions.apiCall("assets/list.php", requestData, function (assetDownloadResult) {
                            $(assetDownloadResult['assets']).each(function (index, element) {
                                if (typeof myApp.data.assetTypes[element['assetTypes_id']] === "undefined") { //Shouldn't realy be needed
                                    myApp.data.assetTypes[element['assetTypes_id']] = element;
                                    myApp.controllers.assets.fullAssetListAppend(element);
                                }
                            });
                            if (Object.keys(assetDownloadResult['assets']).length > 0) {
                                myApp.controllers.assets.barcodeScanAddToList(assetResult.asset.assetTypes_id, assetResult.asset.assets_id);
                            } else {
                                //Asset wasn't found
                                ons.notification.toast("Sorry asset not found - is the correct business set?", { timeout: 2000 });
                            }
                        });
                    } else {
                        myApp.controllers.assets.barcodeScanAddToList(assetResult.asset.assetTypes_id, assetResult.asset.assets_id);
                    }
                }
            });
        },
        barcodeScanAddToList: function(typeid, id) {
            var thisAsset;
            $(myApp.data.assetTypes[typeid]['tags']).each(function (index, element) {
                if (element['assets_id'] == id) {
                    thisAsset = element;
                    return false;
                }
            });
            $("#scanned-list").prepend('<ons-list-item tappable modifier="longdivider"  onclick="document.querySelector(\'#myNavigator\').pushPage(\'assetType.html\', {data: {id: ' + typeid + '}}).then(function() { document.querySelector(\'#myNavigator\').pushPage(\'asset.html\', {data: {id: ' + typeid + ', asset: ' + id + '}}) });">' +
                '<div class="left">' +
                (myApp.data.assetTypes[typeid].thumbnails.length > 0 ? '<img class="list-item__thumbnail" src="' + myApp.data.assetTypes[typeid].thumbnails[0]['url'] + '">' : '<span style="width: 40px;"></span>')+
                '</div>' +
                '<div class="center"><span class="list-item__title">' + thisAsset['assets_tag_format'].replace("-","&#8209;") + " - " + myApp.data.assetTypes[typeid]['assetTypes_name'] + '</span><span class="list-item__subtitle">' + myApp.data.assetTypes[typeid]['assetCategories_name'] + ' - ' + myApp.data.assetTypes[typeid]['manufacturers_name'] + '</span></div>' +
                '<div class="right">' +
                '<div class="list-item__label">' + (typeof myApp.auth.location.name !== "undefined" ? myApp.auth.location.name : "") + '</div>'+
                '</div>' +
                '</ons-list-item>');
            document.querySelector('#myNavigator').pushPage('assetType.html', {data: {id: typeid}}).then(function () {
                document.querySelector('#myNavigator').pushPage('asset.html', {data: {id: typeid, asset: id }});
            });
        },
        fullAssetList: function(done, searchTerm, clear) {
            $("#allAssetsListLoader").show();
            if (typeof clear === undefined) {
                clear = false;
            }
            if (clear) {
                //Clear all assets
                $("#allAssetsList").html("");
                $("#scanned-list").html("");
                myApp.data.assetTypes = {};
                myApp.data.assetTypesPages = null;
            }
            var requestData = {"page": Math.floor(Object.keys(myApp.data.assetTypes).length / 20)+1};
            if (myApp.data.assetTypesPages != null && Object.keys(myApp.data.assetTypes).length > ((myApp.data.assetTypesPages-1)*20)) {
                //Don't allow it to duplicate objects
                $("#allAssetsListLoader").hide();
                done();
            } else {
                if (searchTerm !== undefined && searchTerm != null) {
                    requestData['term'] = searchTerm;
                    requestData['all'] = "true";
                }
                myApp.functions.apiCall("assets/list.php", requestData, function (assetResult) {
                    myApp.data.assetTypesPages = assetResult.pagination.total;
                    $(assetResult['assets']).each(function (index, element) {
                        if (typeof myApp.data.assetTypes[element['assetTypes_id']] === "undefined") {
                            myApp.data.assetTypes[element['assetTypes_id']] = element;
                            myApp.controllers.assets.fullAssetListAppend(element);
                        }
                    });
                    $("#allAssetsListLoader").hide();
                    done();
                }, true);
            }
        },
        fullAssetListAppend: function(element) {
            $("#allAssetsList").append('<ons-list-item tappable modifier="longdivider"  onclick="document.querySelector(\'#myNavigator\').pushPage(\'assetType.html\', {data: {id: ' + element['assetTypes_id'] + '}});">' +
                '<div class="left">' +
                (element.thumbnails.length > 0 ? '<img class="list-item__thumbnail" src="' + element.thumbnails[0]['url'] + '">' : '<span style="width: 40px;"></span>')+
                '</div>' +
                '<div class="center"><span class="list-item__title">' + element['assetTypes_name'] + '</span><span class="list-item__subtitle">' + element['assetCategories_name'] + ' - ' + element['manufacturers_name'] + '</span></div>' +
                '<div class="right">' +
                '<div class="list-item__label">' + (element['tags'].length > 1 ? 'x' + element['tags'].length : element['tags'][0]['assets_tag_format'].replace("-","&#8209;"))+'</div>'+
                '</div>' +
                '</ons-list-item>');
            return true;
        },
        fullAssetListSearch: function(value) {
            myApp.controllers.assets.fullAssetList(function () {
                console.log("Serach complete")
            },value,true);
        },
        fullAssetListPullRefresh: null,
    },
    pages: {
        projectPage: function (data) {
            $("#projectPage-title").html(myApp.data.projects[data.data.id]['projects_name']);
            $("#projectPageTitle").html(myApp.data.projects[data.data.id]['projects_name']);
            $("#projectPageDescription").html("Client: " + myApp.data.projects[data.data.id]['clients_name']);
        },
        newAssetPage: function (data) {
            console.log(data);
        },
        about: function (data) {
            $('.versionNumber').text(myApp.config.version.number);
            $('.versionCode').text(myApp.config.version.code);
            $('#aboutPagePlatform').text(device.platform + " (" + device.model + ")");
        },
        assetTypePage: function (data) {
            var thisAsset = myApp.data.assetTypes[data.data.id];
            if (thisAsset) {
                $("#assetTypePageTitle").html(thisAsset['assetTypes_name']);
                $("#assetTypePageManufacturer").html(thisAsset['manufacturers_name']);
                $("#assetTypePageCategory").html(thisAsset['assetCategories_name']);
                $("#assetTypePageDescription").html(myApp.functions.nl2br(thisAsset['assetTypes_description']));
                $("#assetTypePageProductLink").html(thisAsset['assetTypes_productLink']);
                if (thisAsset['assetTypes_productLink'] !== null) {
                    $("#assetTypePageProductLink").attr("onclick", "cordova.InAppBrowser.open('" + thisAsset['assetTypes_productLink'] + "','_blank')");
                }
                $(thisAsset['tags']).each(function (index, element) {
                    $("#assetTypePageAssetsList").append('<ons-list-item tappable modifier="longdivider"  onclick="document.querySelector(\'#myNavigator\').pushPage(\'asset.html\', {data: {id: ' + data.data.id + ',asset: ' + element['assets_id'] + '}});">' +
                        '<div class="left">' +
                        (element['flagsblocks']["COUNT"]["BLOCK"] > 0 ? '<ons-icon icon="fa-ban" style="color: #dc3545;"></ons-icon>&nbsp;' : '&nbsp;') +
                        (element['flagsblocks']["COUNT"]["FLAG"] > 0 ? '<ons-icon icon="fa-flag" style="color: #ffc107;"></ons-icon>' : '') +
                        '</div>' +
                        '<div class="center">' + element['assets_tag_format'] + '</div>' +
                        '</ons-list-item>');
                });
                //Thumbnails
                var carousel = "";
                $(thisAsset['thumbnails']).each(function (index, element) {
                    console.log(element);
                    carousel += ('<ons-carousel-item><div style="margin-top: 20px;">' +
                        '<img src="' + element.url + '" style="min-width:25%; max-width:100%;height: auto; max-height:65vh;" />' +
                        '</div></ons-carousel-item>');
                });
                $("#assetTypePageCarouselTarget").html(carousel);
                if (carousel != "") {
                    $("#assetTypePageCarousel").parent().show();
                    document.getElementById('assetTypePageCarousel').refresh();
                } else {
                    $("#assetTypePageCarousel").parent().hide();
                }

                //Files
                $("#assetTypePageFilesList").html("");
                if (myApp.auth.instanceHasPermission(54)) {
                    $(thisAsset['files']).each(function (index, element) {
                        console.log(element);
                        $("#assetTypePageFilesList").append('<ons-list-item tappable modifier="longdivider" onclick="myApp.functions.s3url(' + element['s3files_id'] + ',myApp.functions.openBrowser);">' +
                            '<div class="left">' +
                            '<ons-icon icon="' + myApp.functions.fileExtensionToIcon(element['s3files_extension']) + '"></ons-icon>' +
                            '</div>' +
                            '<div class="center">' + element['s3files_name'] + '</div>' +
                            '<div class="right">' + myApp.functions.formatSize(element['s3files_meta_size']) + '</div>' +
                            '</ons-list-item>');
                    });
                }
            } else {

            }
        },
        assetPage: function (data) {
            var thisAssetType = myApp.data.assetTypes[data.data.id];
            var thisAsset;
            $(thisAssetType['tags']).each(function (index, element) {
                if (element['assets_id'] == data.data.asset) {
                    thisAsset = element;
                    return false;
                }
            });
            console.log(thisAsset);
            $("#assetPageTitle").html(thisAsset['assets_tag_format']);
            $("#assetPageNotes").html(myApp.functions.nl2br(thisAsset['assets_notes']));
            $("#assetPageMass").html((thisAsset['assets_mass'] !== null ? thisAsset['assets_mass_format'] : thisAssetType['assetTypes_mass_format']));
            $("#assetPageValue").html((thisAsset['assets_value'] !== null ? thisAsset['assets_value_format'] : thisAssetType['assetTypes_value_format']));
            $("#assetPageWeekRate").html((thisAsset['assets_weekRate'] !== null ? thisAsset['assets_weekRate_format'] : thisAssetType['assetTypes_weekRate_format']));
            $("#assetPageDayRate").html((thisAsset['assets_dayRate'] !== null ? thisAsset['assets_dayRate_format'] : thisAssetType['assetTypes_dayRate_format']));
            $("#assetPageDefinableFields").html("");
            for (i = 1; i <= 10; i++) {
                if (thisAssetType['fields'][i-1] !== "") {
                    $("#assetPageDefinableFields").append('<ons-list-header>' + thisAssetType['fields'][i-1] + '</ons-list-header>' +
                        '        <ons-list-item modifier="nodivider">' +
                        '          <div class="center">' +
                        thisAsset["asset_definableFields_" + i] +
                        // '            <ons-input type="text" value="' + thisAsset["asset_definableFields_" + i] + '" float></ons-input>' +
                        '          </div>' +
                        '        </ons-list-item>');
                }
            }
            $("#assetPageFlagsBlocks").html("");
            $(thisAsset['flagsblocks']['BLOCK']).each(function (index, element) {
                $("#assetPageFlagsBlocks").append('<ons-card>' +
                    '        <div class="title"><ons-icon icon="fa-ban" style="color: #dc3545;"></ons-icon>&nbsp;' +
                    element['maintenanceJobs_title'] +
                    '        </div>' +
                    '        <div class="content">' +
                    element['maintenanceJobs_faultDescription'] +
                    '        </div>' +
                    '      </ons-card>');
            });
            $(thisAsset['flagsblocks']['FLAG']).each(function (index, element) {
                $("#assetPageFlagsBlocks").append('<ons-card>' +
                    '        <div class="title"><ons-icon icon="fa-flag" style="color: #ffc107;"></ons-icon>&nbsp;' +
                    element['maintenanceJobs_title'] +
                    '        </div>' +
                    '        <div class="content">' +
                    element['maintenanceJobs_faultDescription'] +
                    '        </div>' +
                    '      </ons-card>');
            });
            //TODO associate with barcode
            //Files
            $("#assetPageFilesList").html("");
            if (myApp.auth.instanceHasPermission(61)) {
                $(thisAsset['files']).each(function (index, element) {
                    console.log(element);
                    $("#assetPageFilesList").append('<ons-list-item tappable modifier="longdivider" onclick="myApp.functions.s3url(' + element['s3files_id'] + ',myApp.functions.openBrowser);">' +
                        '<div class="left">' +
                        '<ons-icon icon="' + myApp.functions.fileExtensionToIcon(element['s3files_extension']) + '"></ons-icon>' +
                        '</div>' +
                        '<div class="center">' + element['s3files_name'] + '</div>' +
                        '<div class="right">' + myApp.functions.formatSize(element['s3files_meta_size']) + '</div>' +
                        '</ons-list-item>');
                });
            }
        },
    }
}

$(document).ready(function() {
    myApp.config.version.number = "VERSION";
    $('.versionNumber').text(myApp.config.version.number);
    myApp.config.version.code = "VERSION CODE";
    $('.versionCode').text(myApp.config.version.code);

    myApp.auth.token = localStorage.getItem('token');
    if (myApp.auth.token && myApp.auth.token != '') {
        myApp.functions.launchApp();
    } else {
        $("#app-mainview").hide();
        $("#login").show();
    }
});