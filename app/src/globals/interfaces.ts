/* Global Interfaces */

//Asset Types
export interface AssetTypeData { //TODO Finish this
    [index: string]: any; //Index with a string, get something back
    tags: []; //will contain array of tags
    thumnails: []; //array of asset images 
    files: []; //array of asset files
    fields: [];
}

//Assets
export interface AssetData { //TODO Finish this
    [index: string]: any; //Index with a string, get something back
    flagsblocks: {BLOCK:[], FLAG:[], COUNT:{BLOCK: number, FLAG: number}}
}