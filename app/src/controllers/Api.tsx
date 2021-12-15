import axios from 'axios';
import { baseURL } from "../utilities/Auth";

/**
 * Get data from AdamRMS API
 * @param endpoint API endpoint
 * @param data any parameter data for the endpoint
 * @param cancelToken an Axios cancelToken 
 * @returns response data as an Object
 * @link https://adam-rms.com/docs/api/intro for V1 endpoints
 */
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
                console.log(error.response);
            })
    );
}

export default Api;