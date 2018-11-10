<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Scan print</title>
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" id="globalDiv">
    <div class="row" id="qrcode">
        <div class="col-md-4">
        <h2>Scan Qrcode IMEI</h2> 
        <hr> 
        <input type="number" v-model="tid" class="form-control" placeholder="ใส่ TID" autofocus >
        <input type="text" name="name" id="inputID" class="form-control"  v-model="inputQrcode" title="" :disabled="tid.length < 2"
         @keydown.enter="handleSubmit"   ref="inputscan" placeholder="Scan QRcode" >
        <div class="input-group-append">
     <button type="submit" class="btn btn-secondary" @click="reset()">reset</button>
</div>
        </div>
        <div class="col-md-8">
        <br>
            <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between lh-condensed" v-for="model in dataPrint">
                    <div>
                        <h2 class="my-0">{{model.id}}</h2>
                        <h4 class="text-muted">tid : {{model.tid}}</h4>
                        <small class="text-muted">{{model.sn}}</small>
                        
                    </div>
                    <span class="text-muted">
                        <button class="btn btn-info btn-lg" id="print" @click="print(model)">print</button>
                    </span>
                </li>

            </ul>
        </div>
        <table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">imei</th>
      <th>SN</th>
      <th>TID</th>
      <th>#</th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(model,index) in ListData.slice(0, 20)">
      <th scope="row">{{index+1}}</th>
      <td>{{model.imei_id}}</td>
      <td>{{model.sn_details}}</td>
      <td>{{model.tid}}</td>
      <td>
      <button class="btn btn-dark btn-sm" id="print" @click="Reprint(model)">Reprint</button>
      </td>
    </tr>
  </tbody>
</table>
    </div>
   
</div>
<?php 
require __DIR__ . '/vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
$connector = new \Mike42\Escpos\PrintConnectors\WindowsPrintConnector("POS58");
$printer = new Printer($connector);
$id =!empty($_GET['id'])?$_GET['id']:'';
$tid =!empty($_GET['tid'])?$_GET['tid']:'';
$type = Printer::BARCODE_CODE39;
$position = Printer::BARCODE_TEXT_NONE;
if(!empty($id) && !empty($tid)){
   
$printer->setJustification(Printer::JUSTIFY_CENTER);

//$printer->setBarcodeHeight(60);
//$printer->setBarcodeWidth(4);
// $printer->setPrintWidth(20);

$printer->setBarcodeTextPosition($position);
$printer->barcode($id, Printer::BARCODE_CODE93);
$printer->text("tid:".$tid."\n");
$printer->text("imei:".$id);
$printer->feed();
$printer->feed(1);
$printer->feed();

}
$printer->close();
?>
<script src="assets/js/jquery-3.3.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="assets/js/axios.min.js"></script>
<script>
  let el = document.documentElement, rfs =
               el.requestFullScreen
            || el.webkitRequestFullScreen
            || el.mozRequestFullScreen
    ;
    window.addEventListener("offline", function(){
        $('#globalDiv').hide();
        $("#message").html('WARNING: Internet connection has been lost.').show();
    });
    window.addEventListener("online", function(){
        $("#message").empty().hide();
        $('#globalDiv').show();
    });

    let app = new Vue({
        el:"#qrcode",
        data:{
            inputQrcode:"",
            check:"",
            tid:"",
            sending:false,
            dataPrint:[],
            ListData:[]
        },
        mounted(){
           
        },
        created(){
            this.getList()
        },
        methods:{
            Reprint(model){
                location.href="index.php?id=" + model.imei_id+"&tid="+model.tid
                this.After() 
            },
            getList(){
                let vm = this;
                axios.get("http://stock.nextgensoft.biz/api/list-imei").then(function (res) {
                    if(res.status == 200){
                        vm.ListData = res.data
                        console.log(res)
                    }
                })
            },
            print(model){
                location.href="index.php?id=" + model.id+"&tid="+model.tid
                this.After() 
            },
            After(){
                setTimeout(function(){ 
                    console.log("lon")
                    location.href="index.php"
                 }, 1000); 
            },
            handleSubmit(e){
                let vm = this;
                if(e.keyCode == 13 ){
                    if(this.inputQrcode.length === 31 ){
                        vm.sending= true;
                        let getData= this.inputQrcode;
                        let imeiData =  getData.substr(0, 15);
                        let snData =  getData.substr(16);
                        //api เช็ค
                        vm.inputQrcode ="";
                        let dataObject ={
                            "tid":vm.tid,
                            "id":imeiData,
                            "sn":snData
                        }
                        let number_imei= parseInt(imeiData)
                        console.log(typeof(imeiData) )
                        if(dataObject !== "null" && dataObject !== "undefined" && number_imei.toString().length ==15 ){
                            let check =vm.dataPrint.map(function (item) {
                                return item.id;
                            }).indexOf(imeiData);
                            console.log(check);
                            if(check > -1){

                            }else {
                                vm.dataPrint.push(dataObject)

                            }
                            return axios.post("http://stock.nextgensoft.biz/api/save-imei", {
                                "data":dataObject,
                            }).then(function (res) {

                            })
                        }
                        this.getList();
                        vm.inputQrcode ="";
                    }else{
                        console.log("not Data")
                    }
                }
            },
            reset(){
                this.inputQrcode="",
                this.inputQrcode=[]
                location.href="index.php"
            }

        }
    });
    document.onkeypress = function (event) {
        event = event || window.event;
        console.log(event.keyCode)
        if (event.keyCode == 112) {
            $('#inputID').val('');
            console.log('print')
        }
    };
</script>
</body>
</html>