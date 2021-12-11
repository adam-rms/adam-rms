import { IonCard, IonCardContent, IonCardHeader, IonCardSubtitle, IonCardTitle, IonImg, IonItem, IonLabel, IonList, IonSlide, IonSlides } from "@ionic/react";
import { useEffect, useState } from "react";
import { useParams } from "react-router";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import Api from "../../controllers/Api";
import { fileExtensionToIcon, formatSize } from "../../Globals";
import Page from "../../pages/Page";

//Tell typescript some of the format of returned data, so it doesn't get angry
//TODO create interfaces for all api endpoints?
interface AssetData {
    [index: string]: any; //Index with a string, get something back
    tags: []; //will contain array of tags
    thumnails: []; //array of asset images 
    files: []; //array of asset files
}

const AssetList = () => {
    let { id } = useParams<{id: string}>();
    const [assetType, setAssetType] = useState<AssetData>({tags: [], thumnails: [], files: []});

    //get data
    useEffect(() => {
        async function getAssets() {
            const fetchedAssets = await Api("assets/list.php", {"assetTypes_id": id,"all": true});
            setAssetType(fetchedAssets['assets'][0]); //as this is only listing one type, only return one object
        }
        getAssets();
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
                            {assetType.files.map((item :any) => {
                                return (
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
                    <div>
                        <IonCardSubtitle>Manufacturer</IonCardSubtitle>
                        <IonCardTitle>{assetType.manufacturers_name}</IonCardTitle>
                    </div>
                    <div>
                        <IonCardSubtitle>Category</IonCardSubtitle>
                        <IonCardTitle>{assetType.assetCategories_name}</IonCardTitle>
                    </div>
                    {assetType.assetTypes_productLink &&<div>
                        <IonCardSubtitle>Product Link</IonCardSubtitle>
                        <IonCardTitle><a href={assetType.assetTypes_productLink} target="_system">{assetType.assetTypes_productLink}</a></IonCardTitle>
                    </div>}
                    <div>
                        <IonCardSubtitle>Weight</IonCardSubtitle>
                        <IonCardTitle>{assetType.assetTypes_mass_format}</IonCardTitle>
                    </div>
                    <div>
                        <IonCardSubtitle>Value</IonCardSubtitle>
                        <IonCardTitle>{assetType.assetTypes_value_format}</IonCardTitle>
                    </div>
                    <div>
                        <IonCardSubtitle>Day Rate</IonCardSubtitle>
                        <IonCardTitle>{assetType.assetTypes_dayRate_format}</IonCardTitle>
                    </div>
                    <div>
                        <IonCardSubtitle>Week Rate</IonCardSubtitle>
                        <IonCardTitle>{assetType.assetTypes_weekRate_format}</IonCardTitle>
                    </div>
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
                            <IonItem key={item.assets_id}>
                                <IonLabel>
                                    <h2>{item.assets_tag}</h2>
                                    <p>{item.assets_dayRate_format}</p>
                                    <p>{item.assets_weekRate_format}</p>
                                </IonLabel>
                                <IonLabel slot="end">
                                </IonLabel>
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

export default AssetList;