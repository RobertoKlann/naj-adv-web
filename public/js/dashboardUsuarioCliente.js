const NajApi = new Naj();
//---------------------- Functions -----------------------//

$(document).ready(function() {
    loadDataChart()
    loadDataChartBySystem()
    addClassCss('selected', '#sidebar-dashboard');
});

async function loadDataChart(limit = 10, element = false) {
    if (limit == 100)
        limit = '*'

    if (element) {
        $(`.dropdown-item`).removeClass('item-filter-data-chat-selected');
        $(element).addClass('item-filter-data-chat-selected');
    }

    const data = await NajApi.getData(`${baseURL}usuarios/dashboards/data/cliente/${limit}?XDEBUG_SESSION_START`)

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
        bindto: "#operationByCliet",
        size: { height: 400 },
        data: {
            x: 'x',
            columns: items
        },
        padding: {
            top: 40,
            right: 30,
            bottom: 40,
            left: 40,
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

async function loadDataChartBySystem() {
    const data = await NajApi.getData(`${baseURL}usuarios/dashboards/data/system?XDEBUG_SESSION_START`)

    loadChartBySystem(data)
}

function loadChartBySystem(data) {
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
        bindto: "#operationBySystem",
        size: { height: 400 },
        data: {
            x: 'x',
            columns: items
        },
        padding: {
            top: 40,
            right: 30,
            bottom: 40,
            left: 40,
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