import { IonItem, IonLabel, IonList } from "@ionic/react";
import { useEffect, useState } from "react";
import { useParams } from "react-router";
import Api from "../../controllers/Api";
import Page from "../../pages/Page";

const AssetList = () => {
    let { id } = useParams<{id: string}>();

    const [assets, setAssets] = useState([]);

    useEffect(() => {
        async function getAssets() {
            const fetchedAssets = await Api("assets/list.php", {"assetTypes_id": id,"all": true});
            try {
                setAssets(fetchedAssets['assets'][0]['tags']);
            } catch (error) {
                console.log(error);
                setAssets([]);
            }
        }
        getAssets();
    }, []);


    return (
        <Page title="Asset List">
            <IonList>
                {assets.map((item :any) => {
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
        </Page>
    );
}

export default AssetList;