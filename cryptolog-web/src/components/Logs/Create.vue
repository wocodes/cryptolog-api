<template>
    <div class="bg-gray-100">
        <TopBar/>

        <div class="grid grid-cols-5 gap-4">
            <SideBar/>

            <div class="col-span-4 m-6">
                <hr class="m-8">
                <div class="text-left">
                    <h1 class="text-2xl font-bold mb-2 mr-2 inline-block">Add Asset</h1>


                    <form>
                        <div class="px-4 py-5">
                            <div class="grid grid-flow-row grid-cols-3 grid-rows-3 gap-6">
                                <div class="">
                                    <label class="block text-sm font-medium text-gray-700" for="asset-name">Asset
                                        Type</label>
                                    <select  id="asset-name"
                                            v-model="log.asset_id"
                                            class="input-design"
                                            name="asset-name">
                                        <option v-for="asset in assets" :value="asset.id" :key="asset.id">{{ asset.name }}</option>
                                        <!--                                            All this will be changed later-->
                                    </select>
                                </div>

                                <div class="">
                                    <label class="block text-sm font-medium text-gray-700" for="asset-name">Exchange
                                        Name</label>
                                    <select id="exchnage-name"
                                            v-model="log.platform_id"
                                            class="input-design"
                                            name="asset-name">
                                        <option v-for="platform in platforms" :key="platform.id" :value="platform.id">
                                            {{ platform.name }}
                                        </option>
                                        <option :value="null">None</option>
                                        <!--                                            All this will be changed later-->
                                    </select>
                                </div>

                                <div class="">
                                    <label class="block text-sm font-medium text-gray-700" for="quantity">Amount</label>
                                    <input id="quantity" v-model="log.quantity_bought"
                                           class="input-design"
                                           type="text"/>
                                </div>

                                <div class="">
                                    <label class="block text-sm font-medium text-gray-700" for="initial-value">Initial
                                        Value</label>
                                    <input id="initial-value" v-model="log.initial_value"
                                           class="input-design"
                                           type="text"/>
                                </div>

                                <div class="">
                                    <label class="block text-sm font-medium text-gray-700" for="dob">Date of
                                        purchase</label>
                                    <input id="dob"
                                           v-model="log.date_of_purchase"
                                           class="input-design"
                                           type="date"/>
                                </div>
                            </div>
                            <div>
                            </div>
                        </div>
                    </form>
                    <button class="change-button" @click="addAsset" type="submit">Add</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import TopBar from "../TopBar";
import SideBar from "../SideBar";
import Axios from "../../../config/axios";
import TimeAgo from 'javascript-time-ago'
import en from 'javascript-time-ago/locale/en'

TimeAgo.addDefaultLocale(en)

export default {
    name: "AddAsset",
    components: {SideBar, TopBar},
    data() {
        return {
            log: {},
            assets: [],
            platforms: []
        }
    },

    created() {
        Axios.get("/platforms")
            .then(resp => {
                this.platforms = resp.data
            })
            .catch(err => console.error(err));

        Axios.get("/assets")
            .then(resp => {
                this.assets = resp.data
            })
            .catch(err => console.error(err));
    },

    methods: {
        addAsset: function () {
            console.log(this.log)
            Axios.post("/logs", this.log)
                .then(resp => {
                    this.showSuccessToast(resp.data.message)
                })
                .catch(err => console.error(err));
        },
    }
}
</script>

<style scoped>
.input-design {
    @apply mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md;
}

.mute {
    @apply capitalize text-xs text-gray-400;
}

.change-button {
    @apply inline-flex justify-center py-2 px-4 border border-transparent text-sm rounded-md self-end text-white bg-blue-700 font-medium;
}
</style>