import axios from 'axios';
import { baseURL } from "../Globals";

//TODO remove the memory leak from async function by using axios cancel functionality 

const Api = async (endpoint: string, data: {}) => {
    return (
        axios
            .get(baseURL + 'api/' + endpoint, {
                params: data
            })
            .then(function (response) {
                if (response.data['result'] == true){
                    return(response.data['response']);
                } else {
                    return (response.data);
                }
            })
            .catch(function (error) {
                if (error.response) {
                    console.log(error.response);
                }
            })
    );
}

export default Api;