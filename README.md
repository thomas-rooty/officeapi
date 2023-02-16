<img src="https://github.com/thomas-rooty/readXlsx-php-api/blob/master/xlsx-reader.png?raw=true" height="200px" width="auto" alt="XLSX Reader API"/>

# XLSX Reader API
## Description
This PHP API allows you to read, edit, create, calculate cells from an Excel file sent via POST, it'll return the values you asked for to your app.

## How to use Docker
### Build the image
```bash
docker build -t rootkitty/officeapi .
```

### Run the container
```bash
docker run -d -p 80:80 --name officeapi officeapi
```

## JavaScript
```js
async function saveFile(fileName, workbook) {
  const xls64 = await workbook.xlsx.writeBuffer({base64: true})
  const blob = new Blob([xls64], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'})
  const completeName = fileName + '.xlsx';
  saveAs(blob, fileName)

  let formData = new FormData();
  formData.append('file', blob, completeName);

  let xhttp = new XMLHttpRequest();
  xhttp.open("POST", "http://localhost:5050/upload/readXlsx.php", true);

  xhttp.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      console.log(this.responseText);
    }
  };

// Send request with data
  xhttp.send(formData);
}
```
