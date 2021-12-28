import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { IonCol, IonGrid, IonItem, IonLabel, IonRow } from "@ionic/react";
import { MassFormatter, MoneyFormatter } from "../../utilities/Formatters";

const AssetItem = (props: any) => {
  let additionalInfo;
  if (props.item.price) {
    additionalInfo = (
      <>
        <IonCol size="2">
          <IonLabel>{MassFormatter(props.item.assetTypes_mass)}</IonLabel>
        </IonCol>
        <IonCol size="2">
          <IonLabel>
            {MoneyFormatter(props.item.price.currency, props.item.price.amount)}
          </IonLabel>
        </IonCol>
        <IonCol size="2">
          <IonLabel>
            {MoneyFormatter(
              props.item.discountPrice.currency,
              props.item.discountPrice.amount,
            )}
          </IonLabel>
        </IonCol>
      </>
    );
  }
  return (
    <IonItem
      routerLink={"/assets/" + props.AssetTypeId + "/" + props.item.assets_id}
    >
      <IonGrid>
        <IonRow>
          <IonCol size="3">
            <IonLabel>
              <h2>{props.item.assets_tag}</h2>
            </IonLabel>
          </IonCol>
          {additionalInfo}
          <IonCol size="3">
            <FontAwesomeIcon
              icon="arrow-right"
              className="ion-margin-end ion-float-end"
            />
            {props.item.flagsblocks["COUNT"]["BLOCK"] > 0 && (
              <FontAwesomeIcon
                icon="ban"
                color="#dc3545"
                className="ion-margin-end ion-float-end"
              />
            )}
            {props.item.flagsblocks["COUNT"]["FLAG"] > 0 && (
              <FontAwesomeIcon
                icon="flag"
                color="#ffc107"
                className="ion-margin-end ion-float-end"
              />
            )}
          </IonCol>
        </IonRow>
      </IonGrid>
    </IonItem>
  );
};

export default AssetItem;
