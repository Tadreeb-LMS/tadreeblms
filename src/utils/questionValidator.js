/**
 * Question type validator - ensures single-choice allows only one selection.
 */

export function validateSingleChoice(question) {
  const errors = [];
  
  if (question.type !== 'single-choice') {
    return { valid: true, errors: [] };
  }
  
  // Check that only one option is selected
  const selectedCount = question.options.filter(opt => opt.selected).length;
  
  if (selectedCount > 1) {
    errors.push(`Single choice question has ${selectedCount} options selected. Only 1 allowed.`);
    // Auto-fix: keep only first selected option
    let foundFirst = false;
    question.options.forEach(opt => {
      if (opt.selected) {
        opt.selected = foundFirst ? false : true;
        foundFirst = true;
      }
    });
  }
  
  if (selectedCount === 0) {
    errors.push('No option selected for single choice question.');
  }
  
  return { valid: errors.length === 0, errors };
}

export function sanitizeQuestion(question) {
  if (question.type === 'single-choice') {
    validateSingleChoice(question);
  }
  return question;
}