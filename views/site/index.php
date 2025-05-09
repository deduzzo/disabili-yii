<?php

/**
 * @var \yii\web\View $this
 */

use app\assets\MainAsset;
use app\models\Istanza;
use app\models\Movimento;
use app\controllers\ContabilitaController;
use Carbon\Carbon;
use richardfan\widget\JSRegister;
use yii\db\Query;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
$themeMazer = MainAsset::register($this);

// Anno corrente o selezionato
$anno = $anno ?? date('Y');

// Inizializza array vuoti per il grafico (verranno popolati via AJAX)
$spesaMensile = array_fill(0, 12, 0);
$incassoMensile = array_fill(0, 12, 0);
$differenzaMensile = array_fill(0, 12, 0);
$totaleFondi = 0;
$totaleUscite = 0;
$totaleGlobale = 0;

$formatter = \Yii::$app->formatter;

// Get distribution data
$ageDistribution = Istanza::getAgeDistribution();
$iseeDistribution = Istanza::getIseeDistribution();
$districtDistribution = Istanza::getDistrictDistribution();
?>
    <div class="card-content">
        <section class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div
                                            class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon purple mb-2">
                                            <i class="fa fa-hospital-user"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">
                                            Disabili Attivi
                                        </h6>
                                        <h6 class="font-extrabold mb-0"><?= Istanza::getNumIstanzeAttive(); ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div
                                            class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start"
                                    >
                                        <div class="stats-icon blue mb-2">
                                            <i class="fas fa-book-dead"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Deceduti da liquidare</h6>
                                        <h6 class="font-extrabold mb-0"><?= Istanza::getNumDecedutiDaLiquidare(); ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div
                                            class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start"
                                    >
                                        <div class="stats-icon green mb-2">
                                            <i class="fa fa-money-bill"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Pagati ultimo pagamento</h6>
                                        <h6 class="font-extrabold mb-0"><?= Movimento::getNumPagatiUltimoPagamento(); ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div
                                            class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start"
                                    >
                                        <div class="stats-icon red mb-2">
                                            <i class="fa fa-euro-sign"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Totale pagato ultimo pagamento</h6>
                                        <h6 class="font-extrabold mb-0"><?= $formatter->asCurrency(Movimento::getTotalePagatiUltimoPagamento()); ?></h6>
                                        <small class="text-muted">Ultimo pagamento: <?= Yii::$app->formatter->asDate(Movimento::getDataUltimoPagamento(), 'php:d/m/Y') ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Pagamenti vs Entrate (<?= $anno ?>)</h4>
                                <div class="d-flex align-items-center">
                                    <label for="chart-year-selector" class="me-2">Anno:</label>
                                    <select class="form-select form-select-sm" id="chart-year-selector" style="width: auto;">
                                        <?php for ($i = 2017; $i <= date('Y'); $i++): ?>
                                            <option value="<?= $i ?>" <?= $i == $anno ? 'selected' : '' ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Grafico pagamenti vs entrate -->
                                <div id="chart-pagamenti-entrate"></div>

                                <!-- Dati riassuntivi verranno caricati via AJAX -->

                                <!-- Totali -->
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <h5 class="text-center text-danger">Totale Uscite: <?= $formatter->asCurrency($totaleUscite) ?></h5>
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="text-center text-primary">Totale Fondi: <?= $formatter->asCurrency($totaleFondi) ?></h5>
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="text-center text-success">Bilancio: <?= $formatter->asCurrency($totaleGlobale) ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Altre informazioni utili -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Informazioni Utili</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="alert alert-warning">
                                            <h6>Distribuzione età disabili attivi:</h6>
                                            <div id="chart-age-distribution" style="height: 200px;"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-info">
                                            <h6>Distribuzione ISEE disabili attivi:</h6>
                                            <div id="chart-isee-distribution" style="height: 200px;"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-success">
                                            <h6>Distribuzione per distretto:</h6>
                                            <div id="chart-district-distribution" style="height: 200px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php
    $this->registerJsFile("{$themeMazer->baseUrl}/assets/original_assets/extensions/apexcharts/apexcharts.min.js", ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<?php JSRegister::begin(['position' => \yii\web\View::POS_READY]); ?>
<script>
    // Configurazione iniziale del grafico
    var optionsPagamentiEntrate = {
        annotations: {
            position: "back",
        },
        dataLabels: {
            enabled: false,
        },
        chart: {
            type: "bar",
            height: 350,
            stacked: false,
            toolbar: {
                show: true,
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
            },
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left',
        },
        series: [
            {
                name: "Fondi",
                data: <?= json_encode(array_values($incassoMensile)) ?>,
                color: '#435ebe'
            },
            {
                name: "Pagamenti",
                data: <?= json_encode(array_values($spesaMensile)) ?>,
                color: '#dc3545'
            },
            {
                name: "Differenza",
                data: <?= json_encode(array_values($differenzaMensile)) ?>,
                color: '#198754',
                type: 'line'
            }
        ],
        xaxis: {
            categories: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu", "Lug", "Ago", "Set", "Ott", "Nov", "Dic"],
        },
        yaxis: {
            title: {
                text: 'Euro'
            },
            labels: {
                formatter: function (value) {
                    return new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(value);
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (value) {
                    return new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(value);
                }
            }
        },
        fill: {
            opacity: 1
        }
    };

    // Inizializza il grafico
    var chartPagamentiEntrate = new ApexCharts(
        document.querySelector("#chart-pagamenti-entrate"),
        optionsPagamentiEntrate
    );
    chartPagamentiEntrate.render();

    // Funzione per aggiornare i totali
    function updateTotals(data) {
        // Aggiorna i totali visualizzati
        document.querySelector('.text-danger').textContent = 'Totale Uscite: ' +
            new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(data.totaleUscite);
        document.querySelector('.text-primary').textContent = 'Totale Fondi: ' +
            new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(data.totaleFondi);
        document.querySelector('.text-success').textContent = 'Bilancio: ' +
            new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(data.totaleGlobale);
    }

    // Funzione per caricare i dati per un anno specifico
    function loadChartData(anno) {
        fetch('/site/get-chart-data?anno=' + anno)
            .then(response => response.json())
            .then(data => {
                // Verifica che i dati siano validi
                if (!data || typeof data !== 'object') {
                    console.error('Dati non validi ricevuti dal server');
                    return;
                }

                // Aggiorna il titolo del grafico
                document.querySelector('.card-header h4').textContent = 'Pagamenti vs Entrate (' + (data.anno || anno) + ')';

                // Assicurati che i dati per il grafico siano array validi
                const incassoMensile = Array.isArray(data.incassoMensile) ? data.incassoMensile : Array(12).fill(0);
                const spesaMensile = Array.isArray(data.spesaMensile) ? data.spesaMensile : Array(12).fill(0);
                const differenzaMensile = Array.isArray(data.differenzaMensile) ? data.differenzaMensile : Array(12).fill(0);

                // Aggiorna i dati del grafico
                chartPagamentiEntrate.updateSeries([
                    {
                        name: "Fondi",
                        data: incassoMensile,
                        color: '#435ebe'
                    },
                    {
                        name: "Pagamenti",
                        data: spesaMensile,
                        color: '#dc3545'
                    },
                    {
                        name: "Differenza",
                        data: differenzaMensile,
                        color: '#198754',
                        type: 'line'
                    }
                ]);

                // Assicurati che i totali siano numeri validi
                const totaleUscite = typeof data.totaleUscite === 'number' ? data.totaleUscite : 0;
                const totaleFondi = typeof data.totaleFondi === 'number' ? data.totaleFondi : 0;
                const totaleGlobale = typeof data.totaleGlobale === 'number' ? data.totaleGlobale : 0;

                // Aggiorna i totali con i valori validati
                updateTotals({
                    totaleUscite: totaleUscite,
                    totaleFondi: totaleFondi,
                    totaleGlobale: totaleGlobale
                });
            })
            .catch(error => {
                console.error('Errore nel caricamento dei dati:', error);
                // In caso di errore, mostra valori di default
                document.querySelector('.card-header h4').textContent = 'Pagamenti vs Entrate (' + anno + ')';
                chartPagamentiEntrate.updateSeries([
                    {
                        name: "Fondi",
                        data: Array(12).fill(0),
                        color: '#435ebe'
                    },
                    {
                        name: "Pagamenti",
                        data: Array(12).fill(0),
                        color: '#dc3545'
                    },
                    {
                        name: "Differenza",
                        data: Array(12).fill(0),
                        color: '#198754',
                        type: 'line'
                    }
                ]);
                updateTotals({
                    totaleUscite: 0,
                    totaleFondi: 0,
                    totaleGlobale: 0
                });
            });
    }

    // Carica i dati iniziali
    loadChartData(<?= $anno ?>);

    // Gestisce il cambio di anno
    document.getElementById('chart-year-selector').addEventListener('change', function() {
        loadChartData(this.value);
    });

    // Configurazione del grafico per la distribuzione dell'età
    var optionsAgeDistribution = {
        series: [
            <?= $ageDistribution['under18'] ?>,
            <?= $ageDistribution['between18and65'] ?>,
            <?= $ageDistribution['over65'] ?>
        ],
        labels: ["Under 18", "18-65", "Over 65"],
        colors: ["#4CAF50", "#2196F3", "#FF5722"],
        chart: {
            type: "donut",
            height: 200,
            fontFamily: "Nunito, sans-serif",
        },
        legend: {
            position: "bottom",
            fontSize: "12px",
            markers: {
                width: 12,
                height: 12,
                radius: 12
            },
            itemMargin: {
                horizontal: 5,
                vertical: 0
            }
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return value + " persone";
                }
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: "50%",
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Totale',
                            formatter: function (w) {
                                return <?= $ageDistribution['total'] ?>;
                            }
                        }
                    }
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 200
                },
                legend: {
                    position: "bottom"
                }
            }
        }]
    };

    // Inizializza il grafico della distribuzione dell'età
    var chartAgeDistribution = new ApexCharts(
        document.querySelector("#chart-age-distribution"),
        optionsAgeDistribution
    );
    chartAgeDistribution.render();

    // Configurazione del grafico per la distribuzione ISEE
    var optionsIseeDistribution = {
        series: [
            <?= $iseeDistribution['maggiore25k'] ?>,
            <?= $iseeDistribution['minore25k'] ?>,
            <?= $iseeDistribution['no_isee'] ?>
        ],
        labels: ["ISEE > 25K", "ISEE < 25K", "No ISEE"],
        colors: ["#FF9800", "#2196F3", "#9E9E9E"],
        chart: {
            type: "donut",
            height: 200,
            fontFamily: "Nunito, sans-serif",
        },
        legend: {
            position: "bottom",
            fontSize: "12px",
            markers: {
                width: 12,
                height: 12,
                radius: 12
            },
            itemMargin: {
                horizontal: 5,
                vertical: 0
            }
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return value + " persone";
                }
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: "50%",
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Totale',
                            formatter: function (w) {
                                return <?= $iseeDistribution['total'] ?>;
                            }
                        }
                    }
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 200
                },
                legend: {
                    position: "bottom"
                }
            }
        }]
    };

    // Inizializza il grafico della distribuzione ISEE
    var chartIseeDistribution = new ApexCharts(
        document.querySelector("#chart-isee-distribution"),
        optionsIseeDistribution
    );
    chartIseeDistribution.render();

    // Preparazione dati per il grafico dei distretti
    var districtNames = [];
    var districtTotals = [];
    var districtUnder18 = [];
    var districtAdults = [];
    var districtMaggiore25k = [];
    var districtMinore25k = [];

    <?php foreach ($districtDistribution as $id => $data): ?>
        districtNames.push("<?= $data['name'] ?>");
        districtTotals.push(<?= $data['total'] ?>);
        districtUnder18.push(<?= $data['under18'] ?>);
        districtAdults.push(<?= $data['adults'] ?>);
        districtMaggiore25k.push(<?= $data['maggiore25k'] ?>);
        districtMinore25k.push(<?= $data['minore25k'] ?>);
    <?php endforeach; ?>

    // Configurazione del grafico per la distribuzione per distretto
    var optionsDistrictDistribution = {
        series: [{
            name: 'Minori',
            data: districtUnder18
        }, {
            name: 'Adulti',
            data: districtAdults
        }],
        chart: {
            type: 'bar',
            height: 200,
            stacked: true,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                barHeight: '70%',
                dataLabels: {
                    position: 'bottom'
                }
            },
        },
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                // Only show labels for values above 5 to avoid clutter
                return val > 5 ? val : '';
            },
            textAnchor: 'start',
            style: {
                fontSize: '10px',
                colors: ['#fff']
            },
            offsetX: 0
        },
        xaxis: {
            categories: districtNames,
            labels: {
                style: {
                    fontSize: '10px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Distretti'
            },
            labels: {
                style: {
                    fontSize: '10px'
                }
            }
        },
        legend: {
            position: 'bottom',
            fontSize: '10px'
        },
        fill: {
            opacity: 1
        },
        colors: ['#4CAF50', '#2196F3'],
        tooltip: {
            y: {
                formatter: function(value) {
                    return value + " persone";
                }
            }
        }
    };

    // Inizializza il grafico della distribuzione per distretto
    var chartDistrictDistribution = new ApexCharts(
        document.querySelector("#chart-district-distribution"),
        optionsDistrictDistribution
    );
    chartDistrictDistribution.render();
</script>
<?php JSRegister::end(); ?>
