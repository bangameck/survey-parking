$(document).ready(function () {
  // Inisialisasi Select2
  $('#field_coordinator_id').select2({
    theme: 'bootstrap-5'
  });

  // Tampilkan form lokasi setelah koordinator dipilih
  $('#field_coordinator_id').on('change', function () {
    if ($(this).val()) {
      $('#location-forms-container').removeClass('d-none');
    } else {
      $('#location-forms-container').addClass('d-none');
    }
  });

  // Tambah form lokasi baru
  $('#add-location-btn').on('click', function () {
    const newForm = $('#location-list .location-form').first().clone();
    newForm.find('input, textarea').val(''); // Kosongkan input
    newForm.find('.remove-location-btn').prop('disabled', false); // Aktifkan tombol hapus
    $('#location-list').append(newForm);
  });

  // Hapus form lokasi
  $('#location-list').on('click', '.remove-location-btn', function () {
    $(this).closest('.location-form').remove();
  });
});