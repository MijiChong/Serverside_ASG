//Both add function and update function 

// MET values for different exercises
const metValues = {
    'Jogging': 7.0,
    'Gym': 6.0,
    'Cycling': 8.0,
    'Swimming': 8.5,
    'Yoga': 2.5,
    'Basketball': 8.0,
    'Football': 7.0,
    'Tennis': 7.3,
    'Dancing': 4.8,
    'Hiking': 6.0,
    'Walking': 3.5,
    'Running': 9.0,
    'Badminton': 5.5,
    'Volleyball': 4.0,
    'Boxing': 12.0,
    'Martial_arts': 8.0,
    'Pilates': 3.0,
    'Zumba': 6.5,
    'Crossfit': 10.0,
    'Rock_climbing': 8.0,
    'Other': 5.0
};

const intensityMET = {
    'light': 3.0,
    'moderate': 5.0,
    'vigorous': 7.0,
    'very_vigorous': 9.0
};


// Show/hide custom exercise fields
function toggleCustomExercise() {
    const exerciseSelect = document.getElementById('exercise_type');
    const customGroup = document.getElementById('custom-exercise-group');
    const intensityGroup = document.getElementById('intensity-group');

    if (exerciseSelect.value === 'Other') {
        customGroup.style.display = 'block';
        intensityGroup.style.display = 'block';
        document.getElementById('custom_exercise_type').required = true;
    } else {
        customGroup.style.display = 'none';
        intensityGroup.style.display = 'none';
        document.getElementById('custom_exercise_type').required = false;
        document.getElementById('custom_exercise_type').value = '';
    }
    //updateCaloriesField();
}


// Calculate calories burned
function calculateCalories(exerciseType, duration, weight) {
    if (!exerciseType || !duration || !weight) return 0;

    let met;


    if (exerciseType === 'Other') {
        // Use intensity-based MET for custom exercises
        const intensity = document.getElementById('exercise_intensity').value;
        met = intensityMET[intensity] || 5.0;
    } else {
        met = metValues[exerciseType] || 5.0;
    }

    const hours = duration / 60;
    const calories = met * weight * hours;


    return parseFloat(calories.toFixed(2));
}

// Calculate and update calories
function updateCaloriesField() {
    const exerciseType = document.getElementById('exercise_type').value;
    const duration = parseFloat(document.getElementById('duration').value) || 0;
    const weight = parseFloat(document.getElementById('weight').value) || 65;
    const caloriesField = document.getElementById('calories');
    // const caloriesInfo = document.getElementById('calories-info');


    if (exerciseType && duration > 0) {
        const calculatedCalories = calculateCalories(exerciseType, duration, weight);

        // Only update if field is empty or was auto-calculated
        if (!caloriesField.value || caloriesField.classList.contains('calories-auto')) {
            caloriesField.value = calculatedCalories;
            caloriesField.classList.add('calories-auto');
            caloriesField.classList.remove('calories-manual');

            let exerciseName;
            if (exerciseType === 'other') {
                const customName = document.getElementById('custom_exercise_type').value || 'Custom Exercise';
                const intensity = document.getElementById('exercise_intensity');
                const intensityText = intensity.options[intensity.selectedIndex].text.split(' ')[1];
                exerciseName = `${customName} (${intensityText} Intensity)`;
            } else {
                exerciseName = document.querySelector(`#exercise_type option[value="${exerciseType}"]`).textContent.split(' ').slice(1).join(' ');
            }

            // caloriesInfo.textContent = `Auto-calculated for ${exerciseName} (${duration} min, ${weight} kg)`;
        }
    }
}

// Mark calories as manually edited
function markCaloriesAsManual() {
    const caloriesField = document.getElementById('calories');
    const caloriesInfo = document.getElementById('calories-info');

    caloriesField.classList.remove('calories-auto');
    caloriesField.classList.add('calories-manual');
    caloriesInfo.textContent = 'Manually entered - click calculator to auto-calculate';
}

window.addEventListener('DOMContentLoaded', () => {
    const caloriesField = document.getElementById('calories');
    if (caloriesField.value) {
        caloriesField.classList.add('calories-auto');
    }

    // Event listeners for auto-calculation
    document.getElementById('exercise_type').addEventListener('change', function () {
        toggleCustomExercise();
        updateCaloriesField();
    });

    document.getElementById('duration').addEventListener('input', updateCaloriesField);
    document.getElementById('weight').addEventListener('input', updateCaloriesField);
    document.getElementById('recalculate-btn').addEventListener('click', updateCaloriesField);
    document.getElementById('calories').addEventListener('input', markCaloriesAsManual);

    // Event listeners for custom exercise fields
    document.getElementById('custom_exercise_type').addEventListener('input', updateCaloriesField);
    document.getElementById('exercise_intensity').addEventListener('change', updateCaloriesField);

    // Event listener for manual calorie input
    document.getElementById('calories').addEventListener('input', function () {
        if (this.value) {
            markCaloriesAsManual();
        }
    });

    // Recalculate button
    document.getElementById('recalculate-btn').addEventListener('click', function () {
        const caloriesField = document.getElementById('calories');
        caloriesField.classList.remove('calories-manual');
        caloriesField.value = '';
        updateCaloriesField();
    });

    // Set today's date as default
    document.getElementById('exercise_date').valueAsDate = new Date();

    // Clear form
    function clearForm() {
        document.querySelector('#add-exercise form').reset();
        // document.getElementById('exercise_date').value = '<?php echo date("Y-m-d"); ?>';
        toggleCustomExercise();
    }
    updateCaloriesField(); // Run once right away
});

