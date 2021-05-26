/*
[
      '{{repeat(10)}}',
      {
        id: '{{index()}}',
        doorLabel: function (tags) {
          var min = Math.ceil(0);
    var max = Math.floor(2);
    var rand = Math.floor(Math.random() * (max - min + 1)) + min;
          var fruits = ['SAMPLE DOOR DUMMY TEXT BO.123', 'SAMPLE DOOR DUMMY TEXT BO.456', 'SAMPLE DOOR DUMMY TEXT BO.789'];
          return fruits[rand];
        },
        doorCode: '#{{integer(10000, 99999)}}',
        PO: function (tags) {
          var min = Math.ceil(0);
    var max = Math.floor(4);
    var rand = Math.floor(Math.random() * (max - min + 1)) + min;
          var fruits = ['PO-111111-111111', 'PO-222222-222222', 'PO-333333-333333', 'PO-444444-444444', 'PO-555555-555555'];
          return fruits[rand];
        },
        name: '{{company().toUpperCase()}}',
        sku: '{{integer(100, 500)}}-{{integer(1000, 999)}}-{{integer(100, 500)}}',
        sizes: [
          '{{repeat(7)}}',
          {
            qty: '{{integer(1,5)}}',
            barcode: '59068431{{integer(100, 999)}}',
            size: '{{index()+37}}'
          }
        ],
        type: 'style'
      }
    ]
 */