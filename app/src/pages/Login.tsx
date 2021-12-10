import { IonAlert, IonButton, IonButtons, IonCol, IonContent, IonGrid, IonHeader, IonIcon, IonInput, IonItem, IonLabel, IonMenuButton, IonPage, IonRow, IonTitle, IonToolbar } from '@ionic/react';
import { Storage } from '@capacitor/storage';
import { baseURL } from "../constants";
import './Page.css';
import { useHistory } from "react-router";
import { useState } from "react";
import axios from "axios";
import { personCircle } from "ionicons/icons";

const Login: React.FC = () => {
  const history = useHistory();
  const [email, setEmail] = useState<string>("");
  const [password, setPassword] = useState<string>("");
  const [iserror, setIserror] = useState<boolean>(false);
  const [message, setMessage] = useState<string>("");
  const handleLogin = () => {
    if (!email) {
        setMessage("Please enter a valid email");
        setIserror(true);
        return;
    }

    if (!password || password.length < 6) {
        setMessage("Please enter your password");
        setIserror(true);
        return;
    }

    const loginData = {
        "formInput": email,
        "password": password
    }

  axios.post(baseURL + "api/login/authStep2Password.php", JSON.stringify(loginData))
      .then(res => {             
        console.log(res);
          //history.push("/page/Inbox");
       })
       .catch(error=>{
          console.log(error);
          setMessage("Auth failure");
          setIserror(true)
       })
  };

  return (
    <IonPage>
      <IonHeader>
        <IonToolbar>
          <IonTitle>Login</IonTitle>
        </IonToolbar>
      </IonHeader>
      <IonContent fullscreen className="ion-padding ion-text-center">
        <IonGrid>
        <IonRow>
          <IonCol>
            <IonAlert
                isOpen={iserror}
                onDidDismiss={() => setIserror(false)}
                cssClass="my-custom-class"
                header={"Error!"}
                message={message}
                buttons={["Dismiss"]}
            />
          </IonCol>
        </IonRow>
        <IonRow>
          <IonCol>
            <IonIcon
                style={{ fontSize: "70px", color: "#0040ff" }}
                icon={personCircle}
            />
          </IonCol>
        </IonRow>
          <IonRow>
            <IonCol>
            <IonItem>
            <IonLabel position="floating"> Email</IonLabel>
            <IonInput
                type="email"
                value={email}
                onIonChange={(e) => setEmail(e.detail.value!)}
                >
            </IonInput>
            </IonItem>
            </IonCol>
          </IonRow>

          <IonRow>
            <IonCol>
            <IonItem>
              <IonLabel position="floating"> Password</IonLabel>
              <IonInput
                type="password"
                value={password}
                onIonChange={(e) => setPassword(e.detail.value!)}
                >
              </IonInput>
            </IonItem>
            </IonCol>
          </IonRow>
          <IonRow>
            <IonCol>
              <p style={{ fontSize: "small" }}>
                  By clicking LOGIN you agree to our <a href="#">Policy</a>
              </p>
              <IonButton expand="block" onClick={handleLogin}>Login</IonButton>
              <p style={{ fontSize: "medium" }}>
                  Don't have an account? <a href="#">Sign up!</a>
              </p>

            </IonCol>
          </IonRow>
        </IonGrid>
      </IonContent>
    </IonPage>
  );
};

export default Login;
