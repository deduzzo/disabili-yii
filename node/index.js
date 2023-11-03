// script.js
import {flussiRegioneSicilia} from "aziendasanitaria-utils";
import {impostazioniEsterni} from "./config.js";
(async () => {

let flussi = new flussiRegioneSicilia.Assistiti(impostazioniEsterni);
let out = await flussi.verificaDatiAssititoDaNar(["DDMRRT86A03F158E"],false);
    console.log(JSON.stringify(out));
})(); // Aggiunte le parentesi per invocare la funzione