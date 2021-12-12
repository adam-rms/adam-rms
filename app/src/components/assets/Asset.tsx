import { faBan, faFlag } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { IonCard, IonCardContent, IonCardHeader, IonCardSubtitle, IonCardTitle, IonCol, IonContent, IonItem, IonLabel, IonList, IonRow, useIonViewWillLeave } from "@ionic/react";
import axios from "axios";
import { error } from "console";
import { useEffect, useState } from "react";
import { useParams } from "react-router";
import Api from "../../controllers/Api";
import { s3url, fileExtensionToIcon, formatSize } from "../../Globals";
import Page from "../../pages/Page";

//Tell typescript some of the format of returned data, so it doesn't get angry
//TODO create interfaces for all api endpoints?
interface TypeData {
    [index: string]: any; //Index with a string, get something back
    tags: []; //will contain array of tags
    thumnails: []; //array of asset images 
    files: []; //array of asset files
}

interface AssetData{
    [index: string]: any; //Index with a string, get something back
    flagsblocks: {BLOCK:[], FLAG:[], COUNT:{BLOCK: number, FLAG: number}}
}

const Asset = () => {
    let { type, asset } = useParams<{type: string, asset: string}>();
    const [assetType, setAssetType] = useState<TypeData>({tags: [], thumnails: [], files: []});
    const [thisAsset, setThisAsset] = useState<AssetData>({flagsblocks: {BLOCK:[], FLAG:[], COUNT:{BLOCK: 0, FLAG: 0}}});
    let cancelToken = axios.CancelToken.source();

    //get data
    useEffect(() => {
        async function getAssets() {
            const fetchedAssets = await Api("assets/list.php", {"assetTypes_id": type,"all": true}, cancelToken.token);
            try {
                setAssetType(fetchedAssets['assets'][0]);//Store asset type

                //find individual asset in list
                for (let index = 0; index < fetchedAssets['assets'][0]['tags'].length; index++) {
                    const element = fetchedAssets['assets'][0]['tags'][index];
                    if (element.assets_id == parseInt(asset)) {
                        setThisAsset(element);
                        break;
                    }
                }
            } catch (error) {
                console.log("[AdamRMS] " + error );
            }
            
        }
        getAssets();
    }, []);

    useIonViewWillLeave(() => {
        cancelToken.cancel();
    });

    //return page layout
    return (
        <Page title={thisAsset.assets_tag_format}>
            {/* Maintenance */}
            {thisAsset.flagsblocks.BLOCK.map((block: any) => {
                return (
                    <IonCard key={block.maintenanceJobs_id}>
                        <IonCardContent>
                            <IonCardTitle><FontAwesomeIcon icon={faBan} color="#dc3545" /> {block.maintenanceJobs_title}</IonCardTitle>
                            <IonLabel className="container">{block.maintenanceJobs_faultDescription}</IonLabel>
                        </IonCardContent>
                    </IonCard>
                )
            })}
            {thisAsset.flagsblocks.FLAG.map((block: any) => {
                return (
                    <IonCard key={block.maintenanceJobs_id}>
                        <IonCardContent>
                            <IonCardTitle><FontAwesomeIcon icon={faFlag} color="#ffc107" /> {block.maintenanceJobs_title}</IonCardTitle>
                            <IonLabel className="container">{block.maintenanceJobs_faultDescription}</IonLabel>
                        </IonCardContent>
                    </IonCard>
                )
            })}
            
            {/* Asset Notes */}
            <IonCard>
                <IonCardContent>
                    <IonCardTitle>
                        {thisAsset.assets_notes}
                    </IonCardTitle>
                </IonCardContent>
            </IonCard>
            {/* Asset Data */}
            <IonCard>
                <IonCardHeader>
                    <IonCardTitle>Asset Information</IonCardTitle>
                </IonCardHeader>
                <IonCardContent>
                    <IonList>
                        <IonRow>
                            <IonCol>
                                <IonItem>
                                    <div className="container">
                                        <IonCardSubtitle>Mass</IonCardSubtitle>
                                        <IonCardTitle>{thisAsset.assets_mass_format}</IonCardTitle>
                                    </div>
                                </IonItem>
                                <IonItem>
                                    <div className="container">
                                        <IonCardSubtitle>value</IonCardSubtitle>
                                        <IonCardTitle>{thisAsset.assets_value_format}</IonCardTitle>
                                    </div>
                                </IonItem>
                                <IonItem>
                                    <div className="container">
                                        <IonCardSubtitle>Day Rate</IonCardSubtitle>
                                        <IonCardTitle>{thisAsset.assets_dayRate_format}</IonCardTitle>
                                    </div>
                                </IonItem>
                                <IonItem>
                                    <div className="container">
                                        <IonCardSubtitle>Week Rate</IonCardSubtitle>
                                        <IonCardTitle>{thisAsset.assets_weekRate_format}</IonCardTitle>
                                    </div>
                                </IonItem>
                            </IonCol>
                            <IonCol>

                            </IonCol>
                        </IonRow>
                    </IonList>
                </IonCardContent>
            </IonCard>
            {/* Asset Files */}
            { thisAsset.files && thisAsset.files.length > 0 && <IonCard>
                    <IonCardHeader>
                        <IonCardTitle>Asset Files</IonCardTitle>
                    </IonCardHeader>
                    <IonCardContent>
                        <IonList>
                            {thisAsset.files.map(async (item :any) => {
                                return (
                                    <a href={ await s3url(item.s3files_id, item.s3files_meta_size, cancelToken.token)}>
                                        <IonItem key={item.s3files_id}>
                                            <IonLabel slot="start">
                                                <FontAwesomeIcon icon={fileExtensionToIcon(item.s3files_extension)} />
                                            </IonLabel>
                                            <IonLabel>
                                                <h2>{item.s3files_name}</h2>
                                            </IonLabel>
                                            <IonLabel slot="end">
                                                {formatSize(item.s3files_meta_size)}
                                            </IonLabel>
                                        </IonItem>
                                    </a>
                                )
                            })}
                        </IonList>
                    </IonCardContent>
                </IonCard>}
        </Page>
    );
}

export default Asset;