const NajApi = new Naj();
//---------------------- Functions -----------------------//

$(document).ready(function() {
    loadDataChart()
});

async function loadDataChart() {
    const user = JSON.parse(sessionStorage.getItem('@NAJ_WEB/usuario'))
    let data;

    $('#headerUsuario')[0].innerHTML = `Operações do usuário: ${user.nome} - (Último 12 meses)`

    if (user.usuario_tipo_id == 1 || user.usuario_tipo_id == 2) {
        data = await NajApi.getData(`${baseURL}usuarios/estatisticas/data/user/${user.id}?XDEBUG_SESSION_START`)
    } else {
        data = await NajApi.getData(`${baseURL}usuarios/estatisticas/data/${user.id}?XDEBUG_SESSION_START`)
    }


    if (data.length == 0 || data.errorMessage) {
        $('#dashboardUser')[0].style.fontSize = '20px'
        $('#dashboardUser')[0].innerHTML = `Não há informações para este usuário!`

        return NajAlert.toastWarning(data.errorMessage);
    }

    loadChart(data)
}

function loadChart(data) {
    let items = []
    let persons = []

    for (var i = 0; i < data.names.length; i++) {
        let value = []

        value.push(data.names[i])

        if(data.data[data.names[i]]) {
            for(var z = 0; z < data.data[data.names[i]].length; z++) {
                value.push(data.data[data.names[i]][z])
            }
        }

        persons.push(value)
    }

    for (var k = 0; k < persons.length; k++)
        items.push(persons[k])

    items.push(data.period)

    c3.generate({
        bindto: "#dashboardUser",
        size: { height: 440, width: (window.innerWidth - 220) },
        padding: {
            top: 40,
            right: 30,
            bottom: 40,
            left: 50,
        },
        data: {
            x: 'x',
            columns: items
        },
        axis: {
            x: {
                type: 'timeseries',
                tick: {
                    format: '%m/%Y',
                    rotate: 30,
                }
            }
        },
        grid: { y: { show: !0 } }
    });
}