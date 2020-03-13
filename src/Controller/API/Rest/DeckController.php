<?php

declare(strict_types=1);

namespace App\Controller\API\Rest;

use App\Controller\API\BaseController;
use App\Entity\Deck;
use App\Model\DeckDTO;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function array_merge;
use function assert;

/**
 * @Rest\Prefix(value="api/v1")
 * @SWG\Response(
 *    response="200",
 *    description="Success",
 * ),
 * @SWG\Response(
 *    description="Invalid JWT token",
 *    response=401,
 * ),
 */
// TODO привязать(проверка) операции колод к пользователю
class DeckController extends BaseController implements ClassResourceInterface
{
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        parent::__construct(
            $serializer,
            $validator
        );
        $this->serializationGroup = ['deck_list', 'repeatParams_deck'];
    }

    /**
     * @return mixed
     *
     * @SWG\Get(
     *    operationId="getDeckAction",
     *    produces={"application/json"},
     *    summary="Получние колоды пользователя",
     *    description="Получние колоды пользователя",
     *    tags={"DeckController"},
     *    @SWG\Parameter(
     *      in="path",
     *      type="string",
     *      description="id кололды",
     *      name="deck",
     *    ),
     *  @SWG\Schema(
     *    type="object",
     *    @SWG\Property(property="token", type="string"),
     *    @SWG\Property(
     *      property="roles",
     *      type="array",
     *      @SWG\Items(type="string", example="ROLE_USER")
     *    ),
     *    @SWG\Property(property="refresh_token", type="string"),
     *  )
     * )
     */
    public function getAction(Deck $deck)
    {
        $this->denyAccessUnlessGranted('view', $deck);

        return $this->viewSerialized(
            $deck,
            array_merge($this->serializationGroup, ['deck_details', 'card_deck'])
        );
    }

    /**
     * @return mixed
     *
     * @SWG\Get(
     *    operationId="cgetDeckAction",
     *    produces={"application/json"},
     *    summary="Получние всех колод пользователя",
     *    description="Получние всех колод пользователя",
     *    tags={"DeckController"}
     * )
     */
    public function cgetAction()
    {
        $decks = $this
            ->getDoctrine()
            ->getRepository(Deck::class)
            ->findBy(['user' => $this->getUser()]);

        return $this->viewSerialized($decks, $this->serializationGroup);
    }

  /**
   * @return mixed
   *
   * @SWG\Post(
   *    operationId="postDeckAction",
   *    produces={"application/json"},
   *    summary="Создание новой колоды",
   *    description="Создание новой колоды",
   *    tags={"DeckController"},
   *    @SWG\Parameter(
   *      name="Deck",
   *      in="body",
   *      format="application/json",
   *      @SWG\Schema(ref=@Model(type=App\Model\DeckDTO::class))
   *    )
   * )
   */
    public function postAction(Request $request)
    {
        $deck = $this->validateRequestData($request, DeckDTO::class);
        $this->persist($deck);

        return $this->view(['success' => true]);
    }

    /**
     * @SWG\Put(
     *    operationId="putDeckAction",
     *    produces={"application/json"},
     *    summary="Обновление колоды",
     *    description="Обновление колоды",
     *    tags={"DeckController"},
     *    @SWG\Parameter(
     *      name="id",
     *      description="Id колоды",
     *      type="string",
     *      required=true,
     *      in="path"
     *    ),
     *    @SWG\Parameter(
     *      name="New Deck",
     *      in="body",
     *      format="application/json",
     *      @SWG\Schema(ref=@Model(type=App\Model\DeckDTO::class))
     *    )
     * )
     */
    public function putAction(Request $request, Deck $deck) : View
    {
        $this->denyAccessUnlessGranted('edit', $deck);
        $deckDTO = $this->validateRequestData($request, DeckDTO::class);
        assert($deckDTO instanceof DeckDTO);
        $deck = $deckDTO->fromDTO($deck);
        $this->getDoctrine()->getManager()->flush();

        return $this->view(['success' => true]);
    }

  /**
   * @SWG\Patch(
   *    operationId="patchDeckAction",
   *    produces={"application/json"},
   *    summary="Частичное обновление колоды",
   *    description="Частичное обновление колоды",
   *    tags={"DeckController"},
   *    @SWG\Parameter(
   *      name="id",
   *      description="Id колоды",
   *      type="string",
   *      required=true,
   *      in="path"
   *    ),
   *    @SWG\Parameter(
   *      name="Deck",
   *      in="body",
   *      format="application/json",
   *      @SWG\Schema(ref=@Model(type=App\Model\DeckDTO::class))
   *    )
   * )
   */
    public function patchAction(Request $request, Deck $deck) : View
    {
        $this->denyAccessUnlessGranted('edit', $deck);
        $deckDTO = $this->validateRequestData($request, DeckDTO::class);
        assert($deckDTO instanceof DeckDTO);
        $deck = $deckDTO->fromDTO($deck);
        $this->getDoctrine()->getManager()->flush();

        return $this->view(['success' => true]);
    }

    /**
     * @SWG\Delete(
     *    operationId="deletehDeckAction",
     *    produces={"application/json"},
     *    summary="Удаление колоды",
     *    description="Удаление колоды",
     *    tags={"DeckController"},
     *    @SWG\Parameter(
     *      name="id",
     *      description="Id колоды",
     *      type="string",
     *      required=true,
     *      in="path"
     *    ),
     * )
     */
    public function deleteAction(Deck $deck) : View
    {
        $this->denyAccessUnlessGranted('edit', $deck);

        $em = $this->getDoctrine()->getManager();
        $em->remove($deck);
        $em->flush();

        return $this->view(['success' => true]);
    }
}