
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/Modal';
import DangerButton from '@/Components/DangerButton';
import { router } from '@inertiajs/react';

export default function DeleteShopConfirmModal({ shop: { id, name }, maxWidth, IsOpen, closeModal }) {

    const handleDelete = (e) => {
        e.preventDefault();
        router.delete(route('shops.destroy', id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        })
    }

    return (
        <Modal maxWidth={maxWidth} show={IsOpen} onClose={closeModal}>
            <div className='max-w-fit p-4'>
                <h2 className="text-lg font-medium text-gray-900">
                    Удалить магазин {name}?
                </h2>
                <p className="mt-1 mb-4 text-sm text-gray-600">
                    Будет удален ключ Api, рабочие области и списки товаров
                </p>
                <SecondaryButton className="mr-2" onClick={closeModal}>
                    Отмена
                </SecondaryButton>

                <DangerButton onClick={(e) => handleDelete(e)}>
                    Удалить
                </DangerButton>
            </div>
        </Modal>
    );
}