import { faQuestionCircle } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { IonAvatar, IonCard, IonItem, IonLabel, IonList } from "@ionic/react";
import { useContext, useEffect } from "react";
import { AssetTypeContext } from "../../contexts/Asset/AssetTypeContext";
import Page from "../../pages/Page";
import "./Asset.css";

const AssetTypeList = () => {
    const { AssetTypes, refreshAssetTypes } = useContext(AssetTypeContext);

    //Get data from API
    useEffect( () => {
        refreshAssetTypes();
    }, [])

    return (
        <Page title="Asset List">
            <IonCard>
                <IonList>
                    {AssetTypes.map((item : IAssetType) => {
                        return (
                        <IonItem key={item.assetTypes_id} routerLink={"/assets/" + item.assetTypes_id}>
                            {item.thumbnails.length > 0 && <IonAvatar slot="start"><img src={item.thumbnails[0]} alt={item.assetTypes_name} className="imgIcon"></img></IonAvatar>}
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
                    })}
                </IonList>
            </IonCard>
        </Page>
    );
};

export default AssetTypeList;