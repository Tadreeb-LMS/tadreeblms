describe('bootstrap', () => {
    beforeEach(() => {
        jest.resetModules();
        document.head.innerHTML = '<meta name="csrf-token" content="test-token">';
    });

    it('registers the shared frontend globals', () => {
        require('../../resources/js/bootstrap');

        expect(window.$).toBeDefined();
        expect(window.jQuery).toBe(window.$);
        expect(window._).toBeDefined();
        expect(window.swal).toBeDefined();
        expect(window.axios.defaults.headers.common['X-Requested-With']).toBe('XMLHttpRequest');
        expect(window.axios.defaults.headers.common['X-CSRF-TOKEN']).toBe('test-token');
    });
});
