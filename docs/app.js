window.myApp = {};
myApp.config = {
    endpoint: 'http://localhost/',
    //endpoint: 'https://dash.adam-rms.com/',
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
    permissions: [],
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
                url: myApp.config.endpoint + 'api/'+ 'auth/appLogin.php',
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
                        alert("Error connecting to AdamRMS - Please check your connection");
                    } else {
                        alert(request.statusText);
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
    },
    hasPermission(permissionid) {
        if (myApp.auth.token === false || !myApp.data.instance) {
            return false;
        }
        if (myApp.auth.data['permissionsArray'].includes(permissionid)) {
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
                url: myApp.config.endpoint + 'api/' + endpoint,
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
                    alert({title: request.statusText + " - " + status, message: (request.responseText ? request.responseText : 'Error connecting to AdamRMS') }, function () {
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
        myApp.controllers.window.open('/');
    },
    window: {
        open: function(path) {
            $("#pageContent").html("");
            var data = {}
            data['jwt'] = myApp.auth.token;
            if (myApp.data.instanceID !== null) {
                data['instances_id'] = myApp.data.instanceID;
            }
            history.pushState({}, null, path);
            $(".loadingDialog").show();
            $.ajax({
                type: "POST",
                url: myApp.config.endpoint + path,
                dataType: "html",
                data: data,
                success: function (response) {
                    $('.loadingDialog').hide();
                    $("#pageContent").html(response);
                    $("#pageContent").find("a").each(function (index) {
                       var href= $(this).attr("href");
                        if (href && href !== "#" && !$(this).data("toggle") && href.substring(0, 6) !== "mailto") {
                            if (this.host !== window.location.host) {
                                $(this).attr("target","_blank");
                            } else {
                                $(this).attr("href","#");
                                $(this).attr("target","");
                                href = href.replace(myApp.config.endpoint, '');
                                $(this).attr("onclick","myApp.controllers.window.open('" + href + "')");
                            }
                        }
                    });
                },
                error: function (request, status, error) {
                    console.log(error);
                }
            });
        }
    },
    menu: {
        loadNavigation: function () {
            console.log("Loading navigation");
            myApp.functions.apiCall("account/getDetails.php", {}, function (result) {
                //TODO detect if password change needed
                $(".js-auth-data-fill-name").html(result['users_name1'] + " " + result['users_name2']);
                if (result['users_thumbnail']) {
                    myApp.functions.s3url(result['users_thumbnail'], function (url) {
                        $(".js-auth-data-fill-image").attr("src",url);
                    });
                }
                $("#user-calendar-link").attr("onclick","myApp.controllers.window.open('user.php?id=" + result['users_userid'] + "')");
                myApp.auth.permissions = [];
                if (Array.isArray(result['permissions'])) {
                    //Block if not an array
                    var i;
                    var arraylength = result['permissions'].length;
                    for (i = 0; i < arraylength; i++) {
                        if (result['permissions'][i] != null && parseInt(result['permissions'][i]) !== NaN) {
                            myApp.auth.permissions.push(parseInt(result['permissions'][i]));
                        }
                    }
                }
                $(".js-auth-permission[data-permission]").each(function (index, element) {
                    if (myApp.auth.instanceHasPermission($(this).data("permission"))) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                $(".js-auth-not-permission[data-permission]").each(function (index, element) {
                    if (myApp.auth.instanceHasPermission($(this).data("permission"))) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });
            myApp.functions.apiCall("instances/list.php", {}, function (result) {
                if (result.length < 1) {
                    $(".js-hide-no-instance").hide();
                    //TODO add new instance
                } else {
                    $(".js-hide-no-instance").show();
                    myApp.data.instances = [];
                    $("#instanceList").html("");
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
                        $("#instanceList").append('<a href="#" onclick="myApp.auth.changeInstance(' + element['instances_id'] + ')" class="dropdown-item">' +
                               + element['instances_name'] + '<p class="text-sm text-muted">' + element['userInstances_label'] + '</p>' +
                            (element['this'] ? '<div class="d-block d-sm-none"><span class="float-right text-muted text-sm">Selected</span></div>' : '') +
                            '</a><div class="dropdown-divider"></div>');
                        if (element['this']) {
                            //This instance
                            myApp.data.instance = element;
                            myApp.data.instanceID = element['instances_id'];

                            $(".js-instance-fill-name").html(element['instances_name']);
                            document.title = element['instances_name'] + " | AdamRMS";
                            if (myApp.auth.instanceHasPermission(20)) {
                                myApp.functions.apiCall("projects/list.php", {}, function (projectResult) {
                                    if (projectResult) {
                                        $(".js-hide-no-projects").show();
                                    }
                                    $("#projectsList").html("");
                                    $(projectResult).each(function (index, element) {
                                        myApp.data.projects[element['projects_id']] = element;
                                        $("#projectsList").append('<li class="nav-item"><a href="#" onclick="myApp.controllers.window.open(\'project/?id=' + element['projects_id'] + '\')" class="nav-link"><i class="nav-icon ' + (element.thisProjectManager ? 'fas fa-star' : 'far fa-circle') + '"></i><p title="' +element['projects_name'] + '">&nbsp;' +element['projects_name'] + '</p></a></li>');
                                    });
                                });
                            } else {
                                $(".js-hide-no-projects").hide();
                            }
                            $(".js-auth-instance-permission[data-permission]").each(function (index, element) {
                                if (myApp.auth.instanceHasPermission($(this).data("permission"))) {
                                    $(this).show();
                                } else {
                                    $(this).hide();
                                }
                            });
                            $(".js-auth-instance-not-permission[data-permission]").each(function (index, element) {
                                if (myApp.auth.instanceHasPermission($(this).data("permission"))) {
                                    $(this).hide();
                                } else {
                                    $(this).show();
                                }
                            });
                        }
                    });
                }
            });
        }
    },
    assets: {

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

    window.addEventListener('popstate', function(e) {
       console.log(e);
    });
});