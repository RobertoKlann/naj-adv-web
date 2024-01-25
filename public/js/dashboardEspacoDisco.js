const NajApi = new Naj();
//---------------------- Functions -----------------------//

$(document).ready(function() {
    loadChart();

	addClassCss('selected', '#sidebar-dashboard');
});

async function loadChart() {
    let data = await NajApi.getData(`${baseURL}dashboard/espaco/disco/data`);
    let columnLabel = [];
    let columnData = [];

	let medida = 'KB'
	let amount = 0;

	data.map((item) => {
		if(medida == 'GB') return

		if(item.GB > 1) {
			medida = 'GB'
		} else {
			medida = 'MB'
		}
	})

    data.forEach((item) => {
		let value = item.KB

		if (medida == 'GB') {
			value = item.GB
		} else {
			value = item.MB
		}

        columnLabel.push(item.Tabela);
        columnData.push(`${value}`);

		amount = amount + parseFloat(value);
    });

	$('#titleChart')[0].innerHTML = `Utilização do espaço em disco TOTAL: ${amount.toFixed(2)} ${medida}`;
    new Chart(document.getElementById("espacoDisco"), {
		type: 'bar',
		padding: {
            top: 40,
            right: 30,
            bottom: 40,
            left: 40,
        },
		data: {
		  labels: columnLabel,
		  datasets: [
			{
			  label: `Espaço utilizado em ${medida}`,
			  backgroundColor: ["#03a9f4", "#e861ff", "#08ccce", "#e2b35b", "#e40503"],
			  data: columnData
			}
		  ]
		},
		options: {
		  legend: { display: false },
		  title: {
			display: true,
		  }
		}
	});
}