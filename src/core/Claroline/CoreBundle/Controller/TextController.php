<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Form\TextType;
use Claroline\CoreBundle\Entity\User;


/**
 * TextManager will redirect to this controller once a directory is "open" or "edit".
 */
class TextController extends Controller
{
    /**
     * found on https://github.com/paulgb/simplediff/blob/5bfe1d2a8f967c7901ace50f04ac2d9308ed3169/simplediff.php
     * Paul's Simple Diff Algorithm v 0.1
     * (C) Paul Butler 2007 <http://www.paulbutler.org/>
     * May be used and distributed under the zlib/libpng license.
     * This code is intended for learning purposes; it was written with short
     * code taking priority over performance. It could be used in a practical
     * application, but there are a few ways it could be optimized.
     * Given two arrays, the function diff will return an array of the changes.
     * I won't describe the format of the array, but it will be obvious
     * if you use print_r() on the result of a diff on some test data.
     * htmlDiff is a wrapper for the diff command, it takes two strings and
     * returns the differences in HTML. The tags used are <ins> and <del>,
     * which can easily be styled with CSS.
     *
     * @param array $old
     * @param array $new
     *
     * @return array
     */
    public function diff($old, $new)
    {
        $maxlen = 0;

        foreach ($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                        $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if ($matrix[$oindex][$nindex] > $maxlen) {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if ($maxlen == 0) {

            return array(array('d' => $old, 'i' => $new));
        }

        return array_merge(
            $this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)), array_slice($new, $nmax, $maxlen), $this->diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }

    /**
     * wraps the diff between 2 texts with HTML.
     *
     * @param string $old
     * @param string $new
     *
     * @return string
     */
    public function htmlDiff($old, $new)
    {
        $diff = $this->diff(explode(' ', $old), explode(' ', $new));
        $ret = "";

        foreach ($diff as $k) {
            if (is_array($k)) {
                $ret .= (!empty($k['d']) ? "<b style='color:red'>" . implode(' ', $k['d']) . "</b> " : '') .
                        (!empty($k['i']) ? "<b style='color:green'>" . implode(' ', $k['i']) . "</b> " : '');
            } else {
                $ret .= $k . ' ';
            }
        }

        return $ret;
    }

    /**
     * Test method
     *
     * @param string $txt
     *
     * @return string
     */
    public function tokenize($txt)
    {
        $patterns = array();
        $patterns[0] = '/<br \/>/';
        $patterns[1] = '/<p>/';
        $patterns[2] = '/<\/p>/';
        //$patterns[3] = '/\./';

        $replacements = array();
        $replacements[0] = ' &tokenbr ';
        $replacements[1] = ' &tokenbp ';
        $replacements[2] = ' &tokenep ';
        //$replacements[3] = ' &. ';

        $txt = preg_replace($patterns, $replacements, $txt);

        return $txt;
    }

    /**
     * Test method
     *
     * @param string $txt
     *
     * @return string
     */
    public function untokenize($txt)
    {
        $patterns = array();
        $patterns[0] = '/&tokenbr/';
        $patterns[1] = '/&tokenbp/';
        $patterns[2] = '/&tokenep/';
        //$patterns[3] = '/ &. /';

        $replacements = array();
        $replacements[0] = '<br />';
        $replacements[1] = '<p>';
        $replacements[2] = '</p>';
        //$replacements[3] = '.';

        $txt = preg_replace($patterns, $replacements, $txt);

        return $txt;
    }

    /**
     * Show the diff between every text verion. This function is a test.
     *
     * @param integer $textId
     *
     * @return type
     */
    public function historyAction($textId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $text = $em->getRepository('ClarolineCoreBundle:Resource\Text')->find($textId);
        $revisions = $text->getRevisions();
        $size = count($revisions);
        $size--;
        $d = $i = 0;

        while ($i < $size) {
            $new = $revisions[$i]->getContent();
            $i++;
            $old = $revisions[$i]->getContent();

            //$doc = new \DOMDocument();
            //$doc->loadHTML($new);
            //$normalized = $doc->loadHTML($new);
            //var_dump($doc);

            $old = $this->tokenize($old);
            $new = $this->tokenize($new);

            $diff = $this->htmlDiff($old, $new);
            $differences[$d] = $this->untokenize($diff);

            $d++;
        }

        return $this->render('ClarolineCoreBundle:Text:history.html.twig', array('differences' => $differences,
                    'original' => $revisions[$size]->getContent())
        );
    }

    public function editFormAction($textId)
    {
        $text = $this->container->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Resource\Text')->find($textId);
        $content = $this->container->get('templating')->render('ClarolineCoreBundle:Text:edit.html.twig', array('text' => $text->getLastRevision()->getContent(), 'textId' => $textId));
        $response = new Response($content);

        return $response;
    }

    public function editAction($textId)
    {

        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        $text = $request->request->get('content');
        $em = $this->getDoctrine()->getEntityManager();
        $old = $em->getRepository('ClarolineCoreBundle:Resource\Text')->find($textId);
        $version = $old->getVersion();
        $revision = new Revision();
        $revision->setContent($text);
        $revision->setText($old);
        $revision->setVersion(++$version);
        $revision->setUser($user);
        $em->persist($revision);
        $old->setVersion($version);
        $old->setLastRevision($revision);

        $em->flush();

        return new Response('edited');
    }

}