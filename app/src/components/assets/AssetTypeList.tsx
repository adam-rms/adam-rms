import { faQuestionCircle } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { IonAvatar, IonCard, IonIcon, IonItem, IonLabel, IonList, useIonViewWillLeave } from "@ionic/react";
import axios from "axios";
import { useEffect, useState } from "react";
import Api from "../../controllers/Api";
import Page from "../../pages/Page";
import "./Asset.css";


const AssetTypeList = () => {
    const [assetTypes, setAssets] = useState([]);
    let cancelToken = axios.CancelToken.source();

    useEffect(() => {
        async function getAssets() {
            const fetchedAssets = await Api("assets/list.php", {"all": true}, cancelToken.token);
            try {
                setAssets(fetchedAssets['assets']);
            } catch (error) {
                console.log(error);
                setAssets([]);
            }
        }
        getAssets();
    }, []);

    useIonViewWillLeave(() => {
        cancelToken.cancel();
    });

    return (
        <Page title="Asset List">
            <IonCard>
                <IonList>
                    {assetTypes.map((item :any) => {
                        return (
                        <IonItem key={item.assetTypes_id} routerLink={"/assets/" + item.assetTypes_id}>
                            {item.thumbnails.length > 0 && <IonAvatar slot="start"><img src="{item.thumnails[0]}" className="imgIcon"></img></IonAvatar>}
                            {item.thumbnails.length < 1 && <FontAwesomeIcon icon={faQuestionCircle} size="2x" className="imgIcon"/> }
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