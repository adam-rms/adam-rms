import { IonCard, IonCardContent, IonCardHeader, IonCardSubtitle, IonCardTitle, IonCol, IonImg, IonItem, IonLabel, IonList, IonRow, IonSlide, IonSlides, useIonViewWillLeave } from "@ionic/react";
import { useEffect, useRef, useState } from "react";
import { useParams } from "react-router";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import Api from "../../controllers/Api";
import { fileExtensionToIcon, formatSize, s3url } from "../../Globals";
import Page from "../../pages/Page";
import { faArrowRight, faBan, faFlag } from "@fortawesome/free-solid-svg-icons";
import axios from "axios";

//Tell typescript some of the format of returned data, so it doesn't get angry
//TODO create interfaces for all api endpoints?
interface AssetData {
    [index: string]: any; //Index with a string, get something back
    tags: []; //will contain array of tags
    thumnails: []; //array of asset images 
    files: []; //array of asset files
}

const AssetType = () => {
    let { type } = useParams<{type: string}>();
    const [assetType, setAssetType] = useState<AssetData>({tags: [], thumnails: [], files: []});
    let cancelToken = axios.CancelToken.source();

    //get data
    useEffect(() => {
        async function getAssets() {
            const fetchedAssets = await Api("assets/list.php", {"assetTypes_id": type,"all": true}, cancelToken.token);
            try {
                setAssetType(fetchedAssets['assets'][0]); //as this is only listing one type, only return one object
            } catch (error) {
                console.log("[AdamRMS] " + error );
            }
            
        }
        getAssets();
    }, []);

    useIonViewWillLeave(() => {
        cancelToken.cancel();
    });
    

    //generate image carousel
    let images;
    if (assetType.thumnails && assetType.thumnails.length > 0) {
        images = <IonSlides>
                    {assetType.thumbnails.map((image:any) => {
                        return (
                            <IonSlide>
                                <IonImg src={image.url} alt={image.s3files_name} />
                            </IonSlide>
                        )
                    })}
                </IonSlides>;
    }

    //Generate file list
    let files;
    if (assetType.files && assetType.files.length > 0){
        files = <IonCard>
                    <IonCardHeader>
                        <IonCardTitle>Asset Type Files</IonCardTitle>
                    </IonCardHeader>
                    <IonCardContent>
                        <IonList>
                            {assetType.files.map(async (item :any) => {
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
                </IonCard>;
    }

    //return page layout
    return (
        <Page title={assetType.assetTypes_name}>
            {images}
            <IonCard>
                <IonCardContent>
                    <IonRow>
                        <IonCol>
                            <div className="container">
                                <IonCardSubtitle>Manufacturer</IonCardSubtitle>
                                <IonCardTitle>{assetType.manufacturers_name}</IonCardTitle>
                            </div>
                            <div className="container">
                                <IonCardSubtitle>Category</IonCardSubtitle>
                                <IonCardTitle>{assetType.assetCategories_name}</IonCardTitle>
                            </div>
                            {assetType.assetTypes_productLink && <div className="container">
                                <IonCardSubtitle>Product Link</IonCardSubtitle>
                                <IonCardTitle><a href={assetType.assetTypes_productLink} target="_system">{assetType.assetTypes_productLink}</a></IonCardTitle>
                            </div>}
                        </IonCol>
                        <IonCol>
                            <div className="container">
                                <IonCardSubtitle>Weight</IonCardSubtitle>
                                <IonCardTitle>{assetType.assetTypes_mass_format}</IonCardTitle>
                            </div>
                            <div className="container">
                                <IonCardSubtitle>Value</IonCardSubtitle>
                                <IonCardTitle>{assetType.assetTypes_value_format}</IonCardTitle>
                            </div>
                            <div className="container">
                                <IonCardSubtitle>Day Rate</IonCardSubtitle>
                                <IonCardTitle>{assetType.assetTypes_dayRate_format}</IonCardTitle>
                            </div>
                            <div className="container">
                                <IonCardSubtitle>Week Rate</IonCardSubtitle>
                                <IonCardTitle>{assetType.assetTypes_weekRate_format}</IonCardTitle>
                            </div>
                        </IonCol>
                    </IonRow>
                </IonCardContent>
            </IonCard>
            <IonCard>
                <IonCardHeader>
                    <IonCardTitle>Individual Assets</IonCardTitle>
                </IonCardHeader>
                <IonCardContent>
                    <IonList>
                        {assetType.tags.map((item :any) => {
                            return (
                                <IonItem key={item.assets_id} routerLink={"/assets/" + assetType.assetTypes_id + "/" + item.assets_id}>
                                    <IonLabel>
                                        <h2>{item.assets_tag}</h2>
                                    </IonLabel>
                                    <div className="container">{item.flagsblocks['COUNT']['BLOCK'] > 0 && <FontAwesomeIcon icon={faBan} color="#dc3545" />}</div>
                                    <div className="container">{item.flagsblocks['COUNT']['FLAG'] > 0 && <FontAwesomeIcon icon={faFlag} color="#ffc107" />}</div>
                                    <div slot="end">
                                        <FontAwesomeIcon icon={faArrowRight} />
                                    </div>
                                </IonItem>
                            )
                        })}
                    </IonList>
                </IonCardContent>
            </IonCard>
            {files}
        </Page>
    );
}

export default AssetType;