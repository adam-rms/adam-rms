import { faBan, faFlag } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { IonCard, IonCardContent, IonCardHeader, IonCardSubtitle, IonCardTitle, IonCol, IonContent, IonItem, IonLabel, IonList, IonRow, useIonViewWillLeave } from "@ionic/react";
import axios from "axios";
import { useEffect, useState } from "react";
import { useParams } from "react-router";
import Api from "../../controllers/Api";
import { s3url, fileExtensionToIcon, formatSize } from "../../globals/functions";
import { AssetData, AssetTypeData } from "../../globals/interfaces";
import Page from "../../pages/Page";

const Asset = () => {
    let { type, asset } = useParams<{type: string, asset: string}>();
    const [assetType, setAssetType] = useState<AssetTypeData>({tags: [], thumnails: [], files: [], fields: []});
    const [thisAsset, setThisAsset] = useState<AssetData>({flagsblocks: {BLOCK:[], FLAG:[], COUNT:{BLOCK: 0, FLAG: 0}}});
    let cancelToken = axios.CancelToken.source();

    //get data
    useEffect(() => {
        async function getAssets() {
            //reset base values - needed so old data not shown
            setAssetType({tags: [], thumnails: [], files: [], fields: []});
            setThisAsset({flagsblocks: {BLOCK:[], FLAG:[], COUNT:{BLOCK: 0, FLAG: 0}}});
            //query api
            const fetchedAssets = await Api("assets/list.php", {"assetTypes_id": type,"all": true}, cancelToken.token);
            setAssetType(fetchedAssets['assets'][0]);//Store asset type

            //find individual asset in list
            for (let index = 0; index < fetchedAssets['assets'][0]['tags'].length; index++) {
                const element = fetchedAssets['assets'][0]['tags'][index];
                if (element.assets_id == parseInt(asset)) {
                    setThisAsset(element);
                    break;
                }
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
                                {assetType.fields.map((element: {}, index: number) => {
                                    if (assetType.fields[index-1] !== "" && thisAsset["asset_definableFields_" + index] !== "") {
                                        return (
                                            <IonItem key={index}>
                                                <div className="container">
                                                    <IonCardSubtitle>{assetType.fields[index-1]}</IonCardSubtitle>
                                                    <IonCardTitle>{thisAsset["asset_definableFields_" + index]}</IonCardTitle>
                                                </div>
                                            </IonItem>
                                        );
                                    }
                                })}
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