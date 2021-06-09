<template>
    <h1 class="text-2xl text-blue-800 mt-10 text-center">Near Earth Object Status</h1>
        <label for="litepicker" class="block text-center mt-10 mx-auto">
            Enter Date Range
            <svg v-if="loading" class="animate-spin inline-block -mt-2 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </label>

        <div class="relative w-56 mx-auto">
            <div
                class="absolute rounded-l w-10 h-full flex items-center justify-center bg-gray-100 border text-gray-600 border-black">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                     stroke-linejoin="round" class="feather feather-calendar w-4 h-4">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <input type="text" id="litepicker" class="form-control pl-12" data-single-mode="true">
        </div>
    <div class="container flex min-h-screen">
        <div class="flex flex-col m-auto">
            <div v-if="date_range" class="report-chart border-2 bg-white shadow mt-10">
                <canvas id="line-chart" width="600" class="mt-6"></canvas>
            </div>
            <!-- <div class="grid grid-cols-3 gap-4 w-screen"> -->
            <div class="flex mt-4 mb-10">
                <div class="col-span-1 bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
                    <div class="md:flex">
                        <div class="p-8">
                            <div class="uppercase tracking-wide text-sm text-indigo-500 font-semibold">Average
                                Diameter
                            </div>
                            <div class="block mt-1 text-lg leading-tight font-medium text-black hover:underline">
                                {{ average_diameter ? average_diameter + ' km' : '-' }} </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-1 ml-4 bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
                    <div class="md:flex">
                        <div class="p-8">
                            <div class="uppercase tracking-wide text-sm text-indigo-500 font-semibold">Fastest
                                Asteroid
                            </div>
                            <div class="block mt-1 text-lg leading-tight font-medium text-black">
                                {{ fastest_asteroid ? 'Asteroid ID: ' + fastest_asteroid.id : '-' }} <br>
                                {{ fastest_asteroid ? 'Speed: ' + fastest_asteroid.speed + ' kmph' : '' }} </div>

                        </div>
                    </div>
                </div>
                <div class="col-span-1 ml-4 bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
                    <div class="md:flex">
                        <div class="p-8">
                            <div class="uppercase tracking-wide text-sm text-indigo-500 font-semibold">Closest
                                Asteroid
                            </div>
                            <div class="block mt-1 text-lg leading-tight font-medium text-black">
                                 {{ closest_asteroid ? 'Asteroid ID: ' + closest_asteroid.id : '-' }}
                                <br>
                                 {{ closest_asteroid ? 'Approach Date: ' + closest_asteroid.date : '' }} </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Litepicker from "litepicker";
import Chart, {helpers} from "chart.js";

export default {
    data() {
        return {
            loading: false,
            error: false,
            average_diameter: null,
            date_range: null,
            asteroid_count: null,
            fastest_asteroid: null,
            closest_asteroid: null,
        }
    },

    mounted() {
        this.$nextTick(function () {
            this.dateRange = new Litepicker({
                element: document.getElementById('litepicker'),
                autoApply: false,
                autoRefresh: true,
                singleMode: false,
                numberOfColumns: 2,
                numberOfMonths: 2,
                maxDays: 7,
                format: "D MMM YYYY",
                dropdowns: {
                    maxYear: null,
                    months: true,
                    years: true,
                }, setup: (picker) => {
                    picker.on('button:apply', (date1, date2) => {
                        this.getNeoData(date1.format("D MMM YYYY"), date2.format("D MMM YYYY"))
                    });
                },
            });
        });
    },

    methods: {
        getNeoData(start_date, end_date) {
            this.loading = true;
            const self = this;

            axios.get(route('api.neo.data', {start_date: start_date, end_date: end_date}))
                .then(function (response) {
                    self.average_diameter = response.data.average_diameter;
                    self.date_range = response.data.date_range;
                    self.asteroid_count = response.data.asteroid_count;
                    self.fastest_asteroid = response.data.fastest_asteroid;
                    self.closest_asteroid = response.data.closest_asteroid;
                    self.generateChart();
                    self.loading = false;
                })
                .catch(function (error) {
                    self.error = true;
                    self.loading = false;
                });
        },

        generateChart() {
            let context = document.getElementById('line-chart').getContext('2d'),
                chart = new Chart(context, {
                    type: "line",
                    data: {
                        labels: this.date_range,
                        datasets: [
                            {
                                label: "Number of NEOs",
                                data: this.asteroid_count,
                                borderWidth: 2,
                                borderColor: "#3160D8",
                                backgroundColor: "transparent",
                                pointBorderColor: "transparent",
                            },
                        ],
                    },
                    options: {
                        legend: {
                            display: false,
                        },
                        scales: {
                            x: [
                                {
                                    ticks: {
                                        beginAtZero: true,
                                        fontSize: "12",
                                        fontColor: "#777777",
                                    },
                                    gridLines: {
                                        display: false,
                                    },
                                },
                            ],
                            y: [
                                {
                                    ticks: {
                                        beginAtZero: true,
                                        fontSize: "12",
                                        fontColor: "#777777",
                                    },
                                    gridLines: {
                                        color: "#D8D8D8",
                                        zeroLineColor: "#D8D8D8",
                                        borderDash: [2, 2],
                                        zeroLineBorderDash: [2, 2],
                                        drawBorder: false,
                                    },
                                },
                            ],
                        },
                    },
                });
        },
    },
}
</script>
