@props(['isMultiRow' => false])

<v-table>
    {{ $slot }}
</v-table>

@pushOnce('scripts')
    <script type="text/x-template" id="v-table-template">
        <div class="w-full">
            <div class="table-responsive grid w-full shadow-[0px_8px_10px_0px_rgba(0,_0,_0,_0.20)] border-[1px] border-gray-300 rounded-[4px] bg-white overflow-hidden">
                <slot name="header">
                    <template v-if="$parent.isLoading">
                        <x-admin::shimmer.datagrid.table.head :isMultiRow="$isMultiRow"></x-admin::shimmer.datagrid.table.head>
                    </template>

                    <template v-else>
                        <div
                            class="row grid gap-[10px] px-[16px] py-[10px] border-b-[1px] border-gray-300 text-gray-600 bg-gray-50 font-semibold items-center"
                            :style="`grid-template-columns: repeat(${gridsCount}, 1fr)`"
                        >
                            <!-- Mass Actions -->
                            <p v-if="$parent.available.massActions.length">
                                <label for="mass_action_select_all_records">
                                    <input
                                        type="checkbox"
                                        name="mass_action_select_all_records"
                                        id="mass_action_select_all_records"
                                        class="peer hidden"
                                        :checked="['all', 'partial'].includes($parent.applied.massActions.meta.mode)"
                                        @change="$parent.selectAllRecords"
                                    >

                                    <span
                                        class="icon-uncheckbox cursor-pointer rounded-[6px] text-[24px]"
                                        :class="[
                                            $parent.applied.massActions.meta.mode === 'all' ? 'peer-checked:icon-checked peer-checked:text-blue-600' : (
                                                $parent.applied.massActions.meta.mode === 'partial' ? 'peer-checked:icon-checkbox-partial peer-checked:text-blue-600' : ''
                                            ),
                                        ]"
                                    >
                                    </span>
                                </label>
                            </p>

                            <!-- Columns -->
                            <p
                                v-for="column in $parent.available.columns"
                                v-if="$parent.available.actions.length"
                                :class="{'cursor-pointer': column.sortable}"
                                @click="$parent.sortPage(column)"
                            >
                                @{{ column.label }}

                                <i
                                    class="ml-[5px] text-[16px] text-gray-600 align-text-bottom"
                                    :class="[$parent.applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                    v-if="column.index == $parent.applied.sort.column"
                                ></i>
                            </p>

                            <!-- Actions -->
                            <p class="col-start-[none]">
                                @lang('admin::app.components.datagrid.table.actions')
                            </p>
                        </div>
                    </template>
                </slot>

                <slot name="body">
                    <template v-if="$parent.isLoading">
                        <x-admin::shimmer.datagrid.table.body :isMultiRow="$isMultiRow"></x-admin::shimmer.datagrid.table.body>
                    </template>

                    <template v-else>
                        <template v-if="$parent.available.records.length">
                            <div
                                class="row grid gap-[10px] items-center px-[16px] py-[16px] border-b-[1px] border-gray-300 text-gray-600 transition-all hover:bg-gray-100"
                                v-for="record in $parent.available.records"
                                :style="`grid-template-columns: repeat(${gridsCount}, 1fr)`"
                            >
                                <!-- Mass Actions -->
                                <p v-if="$parent.available.massActions.length">
                                    <label :for="`mass_action_select_record_${record[$parent.available.meta.primary_column]}`">
                                        <input
                                            type="checkbox"
                                            class="peer hidden"
                                            :name="`mass_action_select_record_${record[$parent.available.meta.primary_column]}`"
                                            :value="record[$parent.available.meta.primary_column]"
                                            :id="`mass_action_select_record_${record[$parent.available.meta.primary_column]}`"
                                            v-model="$parent.applied.massActions.indices"
                                            @change="$parent.setCurrentSelectionMode"
                                        >

                                        <span class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-[6px] text-[24px] peer-checked:text-blue-600">
                                        </span>
                                    </label>
                                </p>

                                <!-- Columns -->
                                <p
                                    v-for="column in $parent.available.columns"
                                    v-text="record[column.index]"
                                >
                                </p>

                                <!-- Actions -->
                                <p
                                    v-if="$parent.available.actions.length"
                                    class="col-start-[none]"
                                >
                                    <span
                                        class="cursor-pointer rounded-[6px] p-[6px] text-[24px] transition-all hover:bg-gray-100 max-sm:place-self-center"
                                        :class="action.icon"
                                        v-text="!action.icon ? action.title : ''"
                                        v-for="action in record.actions"
                                        @click="$parent.performAction(action)"
                                    >
                                    </span>
                                </p>
                            </div>
                        </template>

                        <template v-else>
                            <div class="row grid px-[16px] py-[16px] border-b-[1px] border-gray-300 text-gray-600 text-center">
                                <p>
                                    @lang('admin::app.components.datagrid.table.no-records-available')
                                </p>
                            </div>
                        </template>
                    </template>
                </slot>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-table', {
            template: '#v-table-template',

            computed: {
                gridsCount() {
                    let count = this.$parent.available.columns.length;

                    if (this.$parent.available.actions.length) {
                        ++count;
                    }

                    if (this.$parent.available.massActions.length) {
                        ++count;
                    }

                    return count;
                },
            },
        });
    </script>
@endpushOnce