<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { TransitionRoot } from '@headlessui/vue';
import { Head, useForm } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import { ref, computed, watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schedule',
        href: '/schedule',
    },
];

interface DaySchedule {
    date: string;
    openTime: string;
    closeTime: string;
    isClosed: boolean;
}

const currentDate = new Date();
const selectedMonth = ref(currentDate.getMonth());
const selectedYear = ref(currentDate.getFullYear());

const form = useForm({
    month: selectedMonth.value + 1, // Adding 1 since getMonth() returns 0-11
    year: selectedYear.value,
    defaultOpenTime: '09:00',
    defaultCloseTime: '17:00',
    schedules: [] as DaySchedule[],
});

const daysInMonth = computed(() => {
    return new Date(selectedYear.value, selectedMonth.value + 1, 0).getDate();
});

const firstDayOfMonth = computed(() => {
    return new Date(selectedYear.value, selectedMonth.value, 1).getDay();
});

const calendarDays = computed(() => {
    const days = [];
    const totalDays = daysInMonth.value;
    const firstDay = firstDayOfMonth.value;

    // Add empty cells for days before the first day of the month
    for (let i = 0; i < firstDay; i++) {
        days.push(null);
    }

    // Add the days of the month
    for (let day = 1; day <= totalDays; day++) {
        const date = new Date(selectedYear.value, selectedMonth.value, day);
        const schedule = form.schedules.find(s => s.date === date.toISOString().split('T')[0]);
        days.push(schedule || {
            date: date.toISOString().split('T')[0],
            openTime: form.defaultOpenTime,
            closeTime: form.defaultCloseTime,
            isClosed: false
        });
    }

    // Add empty cells to complete the last week if needed
    const remainingCells = (7 - (days.length % 7)) % 7;
    for (let i = 0; i < remainingCells; i++) {
        days.push(null);
    }

    return days;
});

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const initializeSchedules = () => {
    const schedules: DaySchedule[] = [];
    for (let day = 1; day <= daysInMonth.value; day++) {
        const date = new Date(selectedYear.value, selectedMonth.value, day);
        schedules.push({
            date: date.toISOString().split('T')[0],
            openTime: form.defaultOpenTime,
            closeTime: form.defaultCloseTime,
            isClosed: false
        });
    }
    form.schedules = schedules;
};

// Watch for month/year changes to reinitialize schedules
watch([() => form.month, () => form.year], () => {
    selectedMonth.value = form.month - 1;
    selectedYear.value = form.year;
    initializeSchedules();
});

const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const updateSchedule = () => {
    console.log(form.schedules);
    // form.post(route('schedule.update'), {
    //     preserveScroll: true,
    //     onSuccess: () => {
    //         // Handle success
    //     },
    //     onError: (errors: any) => {
    //         // Handle errors
    //     },
    // });
};

// Initialize schedules when component is mounted
initializeSchedules();
</script>

<template>
    <Head title="Schedule Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col flex-1 h-full gap-4 p-4 rounded-xl">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min p-5">
                <div class="mb-6">
                    <h2 class="text-lg font-medium">Schedule Management</h2>
                    <p class="text-sm text-gray-600">Set your business hours and closed days for the month</p>
                </div>

                <form @submit.prevent="updateSchedule" class="space-y-6">
                    <!-- Month and Year Selection -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="month">Month</Label>
                            <select
                                id="month"
                                v-model="form.month"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary"
                            >
                                <option v-for="(month, index) in months" :key="index" :value="index + 1">
                                    {{ month }}
                                </option>
                            </select>
                        </div>

                        <div class="grid gap-2">
                            <Label for="year">Year</Label>
                            <Input
                                id="year"
                                v-model="form.year"
                                type="number"
                                class="block w-full mt-1"
                            />
                        </div>
                    </div>

                    <!-- Default Business Hours -->
                    <div class="space-y-4">
                        <h3 class="font-medium text-md">Default Business Hours</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="defaultOpenTime">Default Open Time</Label>
                                <Input
                                    id="defaultOpenTime"
                                    v-model="form.defaultOpenTime"
                                    type="time"
                                    class="block w-full mt-1"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="defaultCloseTime">Default Close Time</Label>
                                <Input
                                    id="defaultCloseTime"
                                    v-model="form.defaultCloseTime"
                                    type="time"
                                    class="block w-full mt-1"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Calendar View -->
                    <div class="space-y-4">
                        <h3 class="font-medium text-md">Monthly Schedule</h3>
                        <div class="overflow-hidden border rounded-lg">
                            <!-- Calendar Header -->
                            <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-800">
                                <div v-for="day in weekDays" :key="day"
                                    class="p-2 text-sm font-semibold text-center text-gray-700 dark:text-gray-300">
                                    {{ day }}
                                </div>
                            </div>

                            <!-- Calendar Grid -->
                            <div class="grid grid-cols-7">
                                <div v-for="(day, index) in calendarDays" :key="index"
                                    class="min-h-[120px] p-2 border-t border-r relative"
                                    :class="{
                                        'bg-gray-50 dark:bg-gray-900/50': !day,
                                        'hover:bg-gray-50 dark:hover:bg-gray-800/50': day
                                    }">
                                    <template v-if="day">
                                        <div class="flex flex-col h-full">
                                            <!-- Date number -->
                                            <div class="text-sm font-medium" :class="{ 'text-gray-400': day.isClosed }">
                                                {{ new Date(day.date).getDate() }}
                                            </div>

                                            <!-- Schedule details -->
                                            <div class="flex flex-col flex-grow mt-1 space-y-2">
                                                <div v-if="!day.isClosed" class="space-y-1 text-xs">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-gray-500">Open:</span>
                                                        <Input
                                                            v-model="day.openTime"
                                                            type="time"
                                                            class="h-6 px-1 text-xs"
                                                        />
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-gray-500">Close:</span>
                                                        <Input
                                                            v-model="day.closeTime"
                                                            type="time"
                                                            class="h-6 px-1 text-xs"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="flex items-center mt-auto">
                                                    <label class="flex items-center gap-1 text-xs">
                                                        <input
                                                            type="checkbox"
                                                            v-model="day.isClosed"
                                                            class="w-3 h-3 border-gray-300 rounded"
                                                        />
                                                        <span>Closed</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">Save Schedule</Button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-neutral-600">Saved</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
