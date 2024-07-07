
            if (myApp.auth.location.type !== false) {
                if (myApp.auth.instanceHasPermission("ASSETS:ASSET_BARCODES:DELETE")) {
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
                                            myApp.functions.apiCall("assets/barcodes/delete.php", {"": assetResult.barcode}, function (result) {
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
        barcodeScanAssociate: function(barcodeid,text,type) {
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
                        if (myApp.auth.instanceHasPermission("ASSETS:EDIT")) {
                            ons.notification.confirm({
                                title: "Change Asset Tag",
                                message: 'Would you like to change the Asset\'s Tag to ' + text + '?',
                                buttonLabels: ['No','Yes']
                            }).then(function (confirm) {
                                if (confirm) {
                                    myApp.functions.apiCall("assets/editAsset.php", {
                                        "assets_id": result['assets_id'],
                                        "assets_tag": text
                                    }, function (result) {
                                        myApp.controllers.assets.barcodeScanPostScan(text,type);
                                    });
                                } else {
                                    myApp.controllers.assets.barcodeScanPostScan(text,type);
                                }
                            });
                        } else {
                            myApp.controllers.assets.barcodeScanPostScan(text,type);
                        }
                    });
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
            /*$("#scanned-list").prepend('<ons-list-item tappable modifier="longdivider"  onclick="document.querySelector(\'#myNavigator\').pushPage(\'assetType.html\', {data: {id: ' + typeid + '}}).then(function() { document.querySelector(\'#myNavigator\').pushPage(\'asset.html\', {data: {id: ' + typeid + ', asset: ' + id + '}}) });">' +
                '<div class="left">' +
                (myApp.data.assetTypes[typeid].thumbnails.length > 0 ? '<img loading="lazy" class="list-item__thumbnail" src="' + myApp.data.assetTypes[typeid].thumbnails[0]['url'] + '">' : '<span style="width: 40px;"></span>')+
                '</div>' +
                '<div class="center"><span class="list-item__title">' + thisAsset['assets_tag_format'].replace("-","&#8209;") + " - " + myApp.data.assetTypes[typeid]['assetTypes_name'] + '</span><span class="list-item__subtitle">' + myApp.data.assetTypes[typeid]['assetCategories_name'] + ' - ' + myApp.data.assetTypes[typeid]['manufacturers_name'] + '</span></div>' +
                '<div class="right">' +
                '<div class="list-item__label">' + (typeof myApp.auth.location.name !== "undefined" ? myApp.auth.location.name : "") + '</div>'+
                '</div>' +
                '</ons-list-item>');*/
            document.querySelector('#myNavigator').pushPage('assetType.html', {data: {id: typeid}}).then(function () {
                document.querySelector('#myNavigator').pushPage('asset.html', {data: {id: typeid, asset: id }});
            });
        },
        