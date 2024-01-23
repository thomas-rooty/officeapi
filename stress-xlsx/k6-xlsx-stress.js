// Auto-generated by the postman-to-k6 converter

import "./libs/shim/core.js";
import http from "k6/http";

export let options = { maxRedirects: 4 };

const Request = Symbol.for("request");
postman[Symbol.for("initial")]({
  options
});

const files = {};
files[
  "C:/Users/thomas.caron/Desktop/Chiffrage contrat EUGENE DELACROIX.xlsx"
] = http.file(
  open(
    "C:/Users/thomas.caron/Desktop/Chiffrage contrat EUGENE DELACROIX.xlsx",
    "b"
  ),
  "Chiffrage contrat EUGENE DELACROIX.xlsx"
);

export default function() {
  postman[Request]({
    name: "Retrieve contract prices",
    id: "10a481e5-e8aa-4582-a963-7a49195df507",
    method: "POST",
    address: "http://wks-docker/api/readXlsx.php",
    data: {
      file:
        files[
          "C:/Users/thomas.caron/Desktop/Chiffrage contrat EUGENE DELACROIX.xlsx"
        ]
    }
  });
}
