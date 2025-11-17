<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import Modal from '../Components/Modal.vue';
import TextInput from '../Components/TextInput.vue';

const tasks = ref([]);
const isLoading = ref(false);
const isCreating = ref(false);
const errorMessage = ref('');
const showCreateModal = ref(false);
const newTaskName = ref('');
const newTaskDueDate = ref('');
const showEditModal = ref(false);
const editingTask = ref(null);
const editTaskName = ref('');
const editTaskDueDate = ref('');
const draggingId = ref(null);
const dropTargetId = ref(null);
const isUpdatingId = ref(null);

function formatDate(dateString) {
	if (!dateString) return '';
	const [year, month, day] = dateString.split('-');
	const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
	return date.toLocaleDateString();
}

async function loadTasks() {
    isLoading.value = true;
    errorMessage.value = '';
    try {
        const { data } = await axios.get('/api/tasks');
        tasks.value = Array.isArray(data) ? data : (data?.data ?? []);
    } catch (e) {
        errorMessage.value = 'Failed to load tasks.';
    } finally {
        isLoading.value = false;
    }
}

function openCreateModal() {
	showCreateModal.value = true;
	newTaskName.value = '';
	newTaskDueDate.value = '';
}

function closeCreateModal() {
	showCreateModal.value = false;
	newTaskName.value = '';
	newTaskDueDate.value = '';
}

function openEditModal(task) {
	editingTask.value = task;
	editTaskName.value = task.name;
	editTaskDueDate.value = task.due_date ? new Date(task.due_date).toISOString().split('T')[0] : '';
	showEditModal.value = true;
}

function closeEditModal() {
	showEditModal.value = false;
	editingTask.value = null;
	editTaskName.value = '';
	editTaskDueDate.value = '';
}

async function createTask(name, dueDate) {
    if (isCreating.value) return;
    isCreating.value = true;
    errorMessage.value = '';
    try {
        const nextOrder = (tasks.value?.length ?? 0) + 1;
        const payload = {
			name: name || 'New Task',
            order: nextOrder,
            completed: false,
            due_date: dueDate,
        };
        const { data } = await axios.post('/api/tasks', payload);
        const created = data?.data ?? data;
        if (created) {
            tasks.value.push(created);
			closeCreateModal();
        } else {
            await loadTasks();
        }
    } catch (e) {
        errorMessage.value = 'Failed to create task.';
    } finally {
        isCreating.value = false;
    }
}

async function saveNewTask() {
	if (!newTaskName.value.trim()) {
		errorMessage.value = 'Please enter a task name.';
		return;
	}
	if (!newTaskDueDate.value) {
		errorMessage.value = 'Please select a due date.';
		return;
	}
	await createTask(newTaskName.value.trim(), newTaskDueDate.value);
}

function onDragStart(task) {
	draggingId.value = task.id;
}

function onDragOver(event, task) {
	event.preventDefault();
	if (draggingId.value !== null && draggingId.value !== task.id) {
		dropTargetId.value = task.id;
	}
}

function onDragEnter(task) {
	if (draggingId.value !== null && draggingId.value !== task.id) {
		dropTargetId.value = task.id;
	}
}

function onDragLeave(event) {
	const relatedTarget = event.relatedTarget;
	if (relatedTarget && !event.currentTarget.contains(relatedTarget)) {
		dropTargetId.value = null;
	}
}

function onDrop(overTask) {
	if (draggingId.value == null || draggingId.value === overTask.id) {
		draggingId.value = null;
		dropTargetId.value = null;
		return;
	}

	const currentIndex = tasks.value.findIndex(t => t.id === draggingId.value);
	const targetIndex = tasks.value.findIndex(t => t.id === overTask.id);
	if (currentIndex === -1 || targetIndex === -1) {
		draggingId.value = null;
		dropTargetId.value = null;
		return;
	}

	const moved = tasks.value.splice(currentIndex, 1)[0];
	tasks.value.splice(targetIndex, 0, moved);

	void persistOrder();

	draggingId.value = null;
	dropTargetId.value = null;
}

async function persistOrder() {
	try {
		const ids = tasks.value.map(t => t.id);
		await axios.post('/api/tasks/reorder', { ids });
	} catch (e) {
		errorMessage.value = 'Failed to save order.';
		await loadTasks();
	}
}

async function toggleCompleted(task) {
	try {
		isUpdatingId.value = task.id;
		const updated = { completed: !task.completed };
		await axios.patch(`/api/tasks/${task.id}`, updated);
		task.completed = !!updated.completed;
	} catch (e) {
		errorMessage.value = 'Failed to update task.';
		await loadTasks();
	} finally {
		isUpdatingId.value = null;
	}
}

async function deleteTask(task) {
	try {
		isUpdatingId.value = task.id;
		await axios.delete(`/api/tasks/${task.id}`);
		const idx = tasks.value.findIndex(t => t.id === task.id);
		if (idx !== -1) tasks.value.splice(idx, 1);
		await persistOrder();
	} catch (e) {
		errorMessage.value = 'Failed to delete task.';
	} finally {
		isUpdatingId.value = null;
	}
}

async function saveEditedTask() {
	if (!editTaskName.value.trim()) {
		errorMessage.value = 'Please enter a task name.';
		return;
	}
	if (!editTaskDueDate.value) {
		errorMessage.value = 'Please select a due date.';
		return;
	}
	if (!editingTask.value) return;

	try {
		isUpdatingId.value = editingTask.value.id;
		const updated = { 
			name: editTaskName.value.trim(),
			due_date: editTaskDueDate.value,
		};
		await axios.patch(`/api/tasks/${editingTask.value.id}`, updated);
		editingTask.value.name = updated.name;
		editingTask.value.due_date = updated.due_date;
		closeEditModal();
	} catch (e) {
		errorMessage.value = 'Failed to update task.';
	} finally {
		isUpdatingId.value = null;
	}
}

onMounted(loadTasks);
</script>

<template>
	<div class="mx-auto max-w-3xl px-4 py-6">
		<div class="space-y-4">
		<div class="flex flex-wrap items-center justify-between gap-2">
			<h2 class="text-lg font-semibold">Tasks</h2>
			<div class="shrink-0">
				<button
					type="button"
					class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
					:disabled="isCreating"
					@click="openCreateModal"
				>
					<span v-if="!isCreating">Create Task</span>
					<span v-else>Creating...</span>
				</button>
			</div>
		</div>

		<p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

		<div v-if="isLoading" class="text-sm text-gray-600">Loading tasks...</div>

		<ul v-else class="divide-y divide-gray-200 rounded-md border border-gray-200">
			<li
				v-for="task in tasks"
				:key="task.id ?? task.name"
				class="flex items-center justify-between gap-3 px-4 py-3 cursor-pointer"
				draggable="true"
				@dragstart="onDragStart(task)"
				@dragover="(event) => onDragOver(event, task)"
				@dragenter="onDragEnter(task)"
				@dragleave="onDragLeave"
				@drop="onDrop(task)"
				@click="(event) => { if (!event.target.closest('input, button')) openEditModal(task); }"
				:style="{
					backgroundColor: draggingId === task.id 
						? 'lightblue' 
						: dropTargetId === task.id 
							? 'yellow' 
							: task.completed 
								? 'lightgreen' 
								: 'white'
				}"
			>
				<div class="flex items-start gap-3 min-w-0">
					<input
						type="checkbox"
						:checked="task.completed"
						@change="toggleCompleted(task)"
						@click.stop
						:disabled="isUpdatingId === task.id"
						class="mt-1 size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
					/>
					<div class="min-w-0">
						<p
							class="truncate text-sm font-medium"
							:class="task.completed ? 'text-gray-500 line-through' : 'text-gray-900'"
						>
							<a :href="`#task-${task.id}`" class="hover:underline">
								{{ task.name }}
							</a>
						</p>
						<div class="flex items-center gap-2 text-xs">
							<p :class="task.completed ? 'text-green-600' : 'text-gray-500'">
								{{ task.completed ? 'Completed' : 'Incomplete' }}
							</p>
							<span v-if="task.due_date" class="text-gray-500">•</span>
							<p v-if="task.due_date" class="text-gray-500">
								Due: {{ formatDate(task.due_date) }}
							</p>
						</div>
					</div>
				</div>
				<div class="flex items-center gap-2">
					<button
						type="button"
						@click.stop="deleteTask(task)"
						:disabled="isUpdatingId === task.id"
						class="rounded-md border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
						title="Delete task"
					>
						Delete
					</button>
				</div>
			</li>
			<li v-if="!tasks.length" class="px-4 py-3 text-sm text-gray-500">
				No tasks yet.
			</li>
		</ul>

		<Modal :show="showCreateModal" maxWidth="md" @close="closeCreateModal">
			<div class="px-6 py-4">
				<h3 class="text-lg font-medium text-gray-900">Create Task</h3>
				<p class="mt-1 text-sm text-gray-500">Enter a name and due date for the new task.</p>
				<div class="mt-4 space-y-4">
					<div>
						<label class="block text-sm font-medium text-gray-700">Task Name <span class="text-red-500">*</span></label>
						<TextInput
							v-model="newTaskName"
							class="mt-1 block w-full"
							placeholder="Task name"
							@keyup.enter="saveNewTask"
							autofocus
							required
						/>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700">Due Date <span class="text-red-500">*</span></label>
						<input
							type="date"
							v-model="newTaskDueDate"
							class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
						/>
					</div>
				</div>
			</div>
			<div class="flex justify-end gap-2 bg-gray-50 px-6 py-3">
				<button
					type="button"
					class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
					@click="closeCreateModal"
				>
					Cancel
				</button>
				<button
					type="button"
					class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
					:disabled="isCreating || !newTaskName.trim() || !newTaskDueDate"
					@click="saveNewTask"
				>
					Save
				</button>
			</div>
		</Modal>

		<Modal :show="showEditModal" maxWidth="md" @close="closeEditModal">
			<div class="px-6 py-4">
				<h3 class="text-lg font-medium text-gray-900">Edit Task</h3>
				<p class="mt-1 text-sm text-gray-500">Update the task name and due date.</p>
				<div class="mt-4 space-y-4">
					<div>
						<label class="block text-sm font-medium text-gray-700">Task Name <span class="text-red-500">*</span></label>
						<TextInput
							v-model="editTaskName"
							class="mt-1 block w-full"
							placeholder="Task name"
							@keyup.enter="saveEditedTask"
							autofocus
							required
						/>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700">Due Date <span class="text-red-500">*</span></label>
						<input
							type="date"
							v-model="editTaskDueDate"
							class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
							required
						/>
					</div>
				</div>
			</div>
			<div class="flex justify-end gap-2 bg-gray-50 px-6 py-3">
				<button
					type="button"
					class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
					@click="closeEditModal"
				>
					Cancel
				</button>
				<button
					type="button"
					class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
					:disabled="isUpdatingId === editingTask?.id || !editTaskName.trim() || !editTaskDueDate"
					@click="saveEditedTask"
				>
					Save
				</button>
			</div>
		</Modal>
		</div>
	</div>
</template>


