import { faQuestionCircle } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { IonAvatar, IonCard, IonImg, IonInfiniteScroll, IonInfiniteScrollContent, IonItem, IonLabel, IonList, IonRefresher, IonRefresherContent, IonTitle } from "@ionic/react";
import { useContext, useEffect } from "react";
import { AssetTypeContext } from "../../contexts/Asset/AssetTypeContext";
import Page from "../../pages/Page";
import "./Asset.css";

/**
 * Asset Type List Page
 * Lists all asset types for a business
 */
const AssetTypeList = () => {
    const { AssetTypes, refreshAssetTypes, getMoreAssets } = useContext(AssetTypeContext);
    
    const doRefresh = (event: CustomEvent) => {
        refreshAssetTypes().then(() => {
            event.detail.complete();
        });
    }

    const loadData = (event: any) => {
        getMoreAssets().then(() => {
            event.target.complete();
        });
    }

    //Get data from API
    useEffect( () => {
        refreshAssetTypes();
    }, [])

    //generate assetList if there are assets
    let assets;
    if (AssetTypes && AssetTypes.assets){
        assets = AssetTypes.assets.map((item : IAssetTypeData) => {
            return (
                <IonItem key={item.assetTypes_id} routerLink={"/assets/" + item.assetTypes_id}>
                    {item.thumbnails.length > 0 && <IonAvatar slot="start"><IonImg src={item.thumbnails[0]} alt={item.assetTypes_name} className="imgIcon"></IonImg></IonAvatar>}
                    {item.thumbnails.length == 0 && <FontAwesomeIcon icon={faQuestionCircle} size="2x" className="imgIcon"/> }
                    <IonLabel>
                        <h2>{item.assetTypes_name}</h2>
                        <p>{item.assetCategories_name}</p>
                    </IonLabel>
                    <IonLabel slot="end">
                        <p>x{item.tags.length}</p>
                    </IonLabel>
                </IonItem>
            )
        })
    } else {
        assets = <IonItem><IonTitle>No Assets Found</IonTitle></IonItem>
    }

    return (
        <Page title="Asset List">
            <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
                <IonRefresherContent/>
            </IonRefresher>
            <IonCard>
                <IonList>
                    {assets}
                </IonList>
            </IonCard>
            <IonInfiniteScroll onIonInfinite={loadData} threshold="100px">
                <IonInfiniteScrollContent loadingSpinner="bubbles" loadingText="Loading more assets..."/>
            </IonInfiniteScroll>
        </Page>
    );
};

export default AssetTypeList;