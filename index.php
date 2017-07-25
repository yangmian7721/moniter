<html>
  <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
      <title>Monitor</title>
	    <link rel="stylesheet" type="text/css" href="css/main.css" />
      <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" >
      <link rel="stylesheet" type="text/css" href="css/jquery.marquee.min.css">
      <link rel="stylesheet" type="text/css" href="http://cdn.bootcss.com/limonte-sweetalert2/5.2.0/sweetalert2.min.css">
      <link rel="stylesheet" type="text/css" href="css/jquery.toast.css">
	  <script src="js/jquery.min.js"></script>
      <script src="js/bootstrap.min.js"></script>
      <script src="js/jquery.marquee.min.js"></script>
      <script src='//cdn.bootcss.com/socket.io/1.3.7/socket.io.js'></script>
      <script src="http://cdn.bootcss.com/limonte-sweetalert2/5.2.0/sweetalert2.min.js"></script>
      <script src="http://echarts.baidu.com/build/dist/echarts.js"></script>
	  <script src="js/jquery.toast.js"></script>
  </head>
  <body style="background-color:#eee;">

  <div>
      <table class="table">
      <tr>
        <td id="td_temp" class="table_td">
          <div  id="temperature" class="shadow">机房温度</div>
        </td>
        <td id="td_out">
          <div  id="outside" class="shadow">出口流量</div>
        </td>
      </tr>
      <tr>
        <td id="td_in" class="table_td">
          <div  id="inside" class="shadow">内部流量</div>
        </td>
        <td id="td_sta">
          <div  id="stability" class="shadow">网络稳定性</div>
        </td>
      </tr>
    </table>
  </div>
  <div class="container" id="downlist">
    <div id="title" class="marquee-sibling">宕机列表</div>
      <div class="marquee">
        <ul id="marquee" class="marquee-content-items">
          <li id="msgs"></li>
        </ul>
      </div>
  </div>

<script type="text/javascript">
var uid = Date.parse(new Date());
console.log(uid);

var notify = new Audio("lib/notify.wav");
var success_ring = new Audio("lib/success.wav");

$(document).ready(function () {
    // 连接服务端
    var socket = io('http://'+document.domain+':2120');
    // 连接后登录
    socket.on('connect', function(){
    	socket.emit('login', uid);
    });
    // 后端推送来消息时
    socket.on('new_msg', function(msg){
		console.log(msg);
		swal({title:'<h3>收到消息</h3>', text: msg.content, timer: 15000,type:msg.hoststatus,showConfirmButton:false});

		if(msg.toast == 'display'){
			$.toast({
				heading: '重要消息',
				hideAfter: false,
				text: msg.content,
				position: 'top-left',
				showHideTransition: 'slide',
				stack: false,
				icon: msg.hoststatus
			});
			notify.play();

		}else if(msg.toast == 'close'){
			$.toast({
				heading: '重要消息',
				hideAfter: 30000,
				text: msg.content,
				position: 'top-left',
				showHideTransition: 'slide',
				stack: false,
				icon: msg.hoststatus
			});
			success_ring.play();

		}else if((msg.hoststatus == 'error' || msg.hoststatus == 'warning') && msg.toast == 'none'){
			notify.play();

		}else if(msg.hoststatus == 'success' && msg.toast == 'none'){
			success_ring.play();

		}

    });
});

/*加载echarts*/
require.config({
    paths: {
        echarts: 'http://echarts.baidu.com/build/dist'
    }
});

/*自动适用屏幕尺寸*/
size_auto();
window.onresize = function(){
  size_auto();
}

/*首次打开时加载数据*/
var first_data = send_ajax();
draw_stability('stability',first_data.stability);
draw_temperature('temperature',first_data.temperature);
draw_outside('outside',first_data.outside);
draw_inside('inside',first_data.inside);
set_msgs(first_data.downlist);



/*定时器,每隔1分钟去加载一次数据*/
var timeTicket;
clearInterval(timeTicket);
timeTicket = setInterval(function (){
  data = send_ajax();
  draw_stability('stability',data.stability);
  draw_temperature('temperature',data.temperature);
  draw_outside('outside',data.outside);
  draw_inside('inside',data.inside);
  set_msgs(data.downlist);

},60000);

/*设置好宕机列表滚动*/
$("#marquee").marquee({
  yScroll: "bottom",
  scrollSpeed: 12,
  showSpeed: 450,
});

/*读取数据函数*/
function send_ajax() {
  var datas;
  $.ajax({
    url:'lib/data.php',
    type:'post',
    async:false,
    dataType:'json',
    success:function(data){
      datas = data;
    },
    error:function(msg) {
      swal({title:'<h3>读取数据出错</h3>', text: '看到些消息请不必惊慌', timer: 5000,type:'error',showConfirmButton:false});
    }
  });
  return datas;
}

/*自动设置尺寸函数*/
function size_auto() {
  var td_temp = document.getElementById('td_temp');
  var td_in = document.getElementById('td_in');
  var td_out = document.getElementById('td_out');
  var td_sta = document.getElementById('td_sta');
  var downlist = document.getElementById('downlist');
  var downlist_h = $("#downlist").height();
  var height = document.body.clientHeight;
  td_temp.setAttribute('height',(height-downlist_h)/2);
  td_in.setAttribute('height',(height-downlist_h)/2);
  td_out.setAttribute('height',(height-downlist_h)/2);
  td_sta.setAttribute('height',(height-downlist_h)/2);
  downlist.setAttribute('height',downlist_h);
  if(!(window.outerHeigth==screen.heigth && window.outerWidth==screen.width)){
    swal({title:'<h3>当前显示非全屏,请调整浏览器显示为全屏模式！</h3>', timer: 3000,type:'info',showConfirmButton:false});
  }
}

/*格式化echarts数据*/
function format_bytes (sizeb) {
    var size;
    sizekb = 8*sizeb / 1024;
    sizemb = sizekb / 1024;
    sizegb = sizemb / 1024;
    sizetb = sizegb / 1024;
    sizepb = sizetb / 1024;
    if (sizeb > 1) {size = Math.round(sizeb) + "B";}
    if (sizekb > 1) {size = Math.round(sizekb) + "K";}
    if (sizemb > 1) {size = Math.round(sizemb) + "M";}
    if (sizegb > 1) {size = Math.round(sizegb) + "G";}
    if (sizetb > 1) {size = Math.round(sizetb) + "T";}
    if (sizepb > 1) {size = Math.round(sizepb) + "P";}
    return size;
  }

function draw_stability(id,data) {
  require(
      [
          'echarts',
          'echarts/chart/line'
      ],
      function (ec) {
          var myChart = ec.init(document.getElementById(id));
          option = {
            backgroundColor : '#fff',
            animation : false,
              title : {
                  show : true,
                  text: '集团网络稳定性趋势表',
                  x:'center',
                  textStyle:{
                     fontSize: 30,
                     fontWeight: 'nomal',
                     color: '#333'
                }
              },
              tooltip : {
                  trigger: 'axis'
              },
              legend: {
                  data:data.legend,
                  y:'20px',
                  itemWidth:10,//改变图例图标的宽度
                  textStyle:{
                     fontSize: 28,
                     fontWeight: 'nomal',
                     color: '#333'
                }
              },
              calculable : true,
              xAxis : [
                  {
                      type : 'category',
                      boundaryGap : false,
                      data : data.xAxis,
                      axisLabel:{
                        textStyle:{
                              fontSize: 12,
                              fontWeight: 'nomal',
                              color: '#333'
                      }
                    }
                  }
              ],
              yAxis : [
                  {
                      type : 'value',
                      min : 90,
                      max : 100,
                      axisLabel : {
                          margin:2,
                          formatter: '{value}%',
                          textStyle:{
                                  fontSize: 26,
                                  fontWeight: 'nomal',
                                  color: '#333'
                          }
                      }
                  }
              ],
              grid :{
                x : 35,
                y : 45,
                x2 : 15,
                y2 : 30
              },
              series : [
                  {
                      name:data.legend[0],
                      symbol:'none',  //这句就是去掉点的
                      type:'line',
                      smooth:true,  //这句就是让曲线变平滑的
                      data:data.core
                  },
                  {
                      name:data.legend[1],
                      symbol:'none',  //这句就是去掉点的
                      type:'line',
                      smooth:true,  //这句就是让曲线变平滑的
                      data:data.access
                  },
                  {
                      name:data.legend[2],
                      symbol:'none',  //这句就是去掉点的
                      type:'line',
                      smooth:true,  //这句就是让曲线变平滑的
                      data:data.vpn,
                      itemStyle:{
                        normal:{
                          areaStyle:{
                            type:'default'
                          }
                        }
                      }
                  }
              ]
          };
          myChart.setOption(option);
      }
  );

}
function draw_temperature(id,data) {
  require(
      [
          'echarts',
          'echarts/chart/line'
      ],
      function (ec) {
          var myChart = ec.init(document.getElementById(id));
          option = {
            backgroundColor : '#fff',
            animation : false,
              title : {
                  show : true,
                  text: '集团各机房温度表',
                  x:'center',
                  textStyle:{
                     fontSize: 30,
                     fontWeight: 'nomal',
                     color: '#333'
                }
              },
              tooltip : {
                  trigger: 'axis'
              },
              legend: {
                  data:data.legend,
                  y:'20px',
                  itemWidth:10,//改变图例图标的宽度
                  textStyle:{
                     fontSize: 28,
                     fontWeight: 'nomal',
                     color: '#333'
                }
              },
              calculable : true,
              xAxis : [
                  {
                      type : 'category',
                      boundaryGap : false,
                      data : data.xAxis,
                      axisLabel:{
                        textStyle:{
                              fontSize: 12,
                              fontWeight: 'nomal',
                              color: '#333'
                      }
                    }
                  }
              ],
              yAxis : [
                  {
                      type : 'value',
                      min : 20,
                      max : 60,
                      axisLabel : {
                          margin:2,
                          formatter: '{value}°C',
                          textStyle:{
                                  fontSize: 26,
                                  fontWeight: 'nomal',
                                  color: '#333'
                          }
                      }
                  }
              ],
              grid :{
                x : 35,
                y : 45,
                x2 : 15,
                y2 : 30
              },
              series : [
                  {
                      name:data.legend[0],
                      symbol:'none',  //这句就是去掉点的
                      type:'line',
                      smooth:true,  //这句就是让曲线变平滑的
                      data:data.water
                  },
                  {
                      name:data.legend[1],
                      symbol:'none',  //这句就是去掉点的
                      type:'line',
                      smooth:true,  //这句就是让曲线变平滑的
                      data:data.joyful
                  },
                  {
                      name:data.legend[2],
                      symbol:'none',  //这句就是去掉点的
                      type:'line',
                      smooth:true,  //这句就是让曲线变平滑的
                      data:data.admin
                  },
                  {
                      name:data.legend[3],
                      symbol:'none',  //这句就是去掉点的
                      type:'line',
                      smooth:true,  //这句就是让曲线变平滑的
                      data:data.club
                    },{
                      name:data.legend[4],
                      symbol:'none',  //这句就是去掉点的
                      type:'line',
                      smooth:true,  //这句就是让曲线变平滑的
                      data:data.mingdu
                  }
              ]
          };
          myChart.setOption(option);
      }
  );
}
function draw_outside(id,data) {
  require(
      [
          'echarts',
          'echarts/chart/line'
      ],
      function (ec) {
          var myChart = ec.init(document.getElementById(id));
          option = {
            backgroundColor : '#fff',
            animation : false,
              title : {
                  show : true,
                  text: '集团各出口流量表',
                  x:'center',
                  textStyle:{
                     fontSize: 30,
                     fontWeight: 'nomal',
                     color: '#333'
                }
              },
              tooltip : {
                  trigger: 'axis'
              },
              legend: {
                  data:data.legend,
                  y:'20px',
                  itemWidth:10,//改变图例图标的宽度
                  textStyle:{
                     fontSize: 28,
                     fontWeight: 'nomal',
                     color: '#333'
                }
              },
              calculable : true,
              xAxis : [
                  {
                      type : 'category',
                      boundaryGap : false,
                      data : data.xAxis,
                      axisLabel:{
                        textStyle:{
                              fontSize: 12,
                              fontWeight: 'nomal',
                              color: '#333'
                      }
                    }
                  }
              ],
              yAxis : [
                  {
                      type : 'value',
                      axisLabel : {
                          margin:2,
                          formatter: function(value){
                            return  format_bytes(value);
                          },
                          textStyle:{
                                  fontSize: 12,
                                  fontWeight: 'nomal',
                                  color: '#333'
                          }
                      }
                  }
              ],
              grid :{
                x : 35,
                y : 45,
                x2 : 15,
                y2 : 30
              },
              series : [
                  {
                      name:data.legend[0],
                      z:100,
                      symbol:'none',  //这句就是去掉点的
                      type:'line',
                      smooth:true,  //这句就是让曲线变平滑的
                      data:data.dx_in
                  },
                  {
                      name:data.legend[1],
                      symbol:'none',
                      type:'line',
                      smooth:true,
                      data:data.dx_out,
                      itemStyle:{
                        normal:{
                          areaStyle:{
                            type:'default'
                          }
                        }
                      }
                  },
                  {
                      name:data.legend[2],
                      z:100,
                      symbol:'none',
                      type:'line',
                      smooth:true,
                      data:data.lt_in
                  },
                  {
                      name:data.legend[3],
                      symbol:'none',
                      type:'line',
                      smooth:true,
                      data:data.lt_out,
                      itemStyle:{
                        normal:{
                          areaStyle:{
                            type:'default'
                          }
                        }
                      }
                  }
              ]
          };
          myChart.setOption(option);
      }
  );
}
function draw_inside(id,data) {
  require(
      [
          'echarts',
          'echarts/chart/line'
      ],
      function (ec) {
          var myChart = ec.init(document.getElementById(id));
          option = {
            backgroundColor : '#fff',
            animation : false,
              title : {
                  show : true,
                  text: '集团各分支总流量表',
                  x:'center',
                  textStyle:{
                     fontSize: 30,
                     fontWeight: 'nomal',
                     color: '#333'
                }
              },
              tooltip : {
                  trigger: 'axis'
              },
              legend: {
                  data:data.legend,
                  y:'20px',
                  itemWidth:10,//改变图例图标的宽度
                  textStyle:{
                     fontSize: 28,
                     fontWeight: 'nomal',
                     color: '#333'
                }
              },
              calculable : true,
              xAxis : [
                  {
                      type : 'category',
                      boundaryGap : false,
                      data : data.xAxis,
                      axisLabel:{
                        textStyle:{
                              fontSize: 12,
                              fontWeight: 'nomal',
                              color: '#333'
                      }
                    }
                  }
              ],
              yAxis : [
                  {
                      type : 'value',
                      axisLabel : {
                          margin:2,
                          formatter: function(value){
                            return  format_bytes(value);
                          },
                          textStyle:{
                                  fontSize: 12,
                                  fontWeight: 'nomal',
                                  color: '#333'
                          }
                      }
                  }
              ],
              grid :{
                x : 35,
                y : 45,
                x2 : 15,
                y2 : 30
              },
              series : [
                  {
                      name:data.legend[0],
                      symbol:'none',  //这句就是去掉点的
                      type:'line',
                      smooth:true,  //这句就是让曲线变平滑的
                      data:data.all_254,
                      z:100

                  },
                  {
                      name:data.legend[1],
                      symbol:'none',
                      type:'line',
                      smooth:true,
                      data:data.all_253,
                      itemStyle:{
                        normal:{
                          areaStyle:{
                            type:'default'
                          }
                        }
                      }
                  },
                  {
                      name:data.legend[2],
                      symbol:'none',
                      type:'line',
                      smooth:true,
                      data:data.all_252,
                      z:100
                  },
                  {
                      name:data.legend[3],
                      symbol:'none',
                      type:'line',
                      smooth:true,
                      data:data.all_251,
                      itemStyle:{
                        normal:{
                          areaStyle:{
                            type:'default'
                          }
                        }
                      }
                  },
                  {
                      name:data.legend[4],
                      symbol:'none',
                      type:'line',
                      smooth:true,
                      data:data.all_249,
                      z:100
                  }
              ]
          };
          myChart.setOption(option);
      }
  );

}

/*设置downlist内容*/
function set_msgs(dates) {
    $("#msgs").html(dates.msg);
    $("#title").html("宕机总数("+dates.downs+")");

  if(dates.downs<=1){
      document.getElementById('downlist').style.background="#6CC6FB";
    }else if(dates.downs>=2 && dates.downs<=5){
      document.getElementById('downlist').style.background="#EE5020";
    }else if(dates.downs>5){
      document.getElementById('downlist').style.background="#FF0000";
    }
}
</script>
  </body>
</html>
